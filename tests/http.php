<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

[$db, $database, $environment] = testDatabase();
$root = dirname(__DIR__);
$runtime = $root . '/.runtime';
if (!is_dir($runtime . '/sessions')) { mkdir($runtime . '/sessions', 0700, true); }
$command = getenv('TEST_APACHE')
    ? [getenv('TEST_APACHE'), '-f', $root . '/tests/httpd-test.conf']
    : [PHP_BINARY, '-d', 'session.save_path=' . $runtime . '/sessions', '-S', '127.0.0.1:18086', '-t', $root, __DIR__ . '/router.php'];
$server = proc_open($command, [0 => ['pipe', 'r'], 1 => ['file', $runtime . '/http-test.log', 'a'], 2 => ['file', $runtime . '/http-test.log', 'a']], $pipes, $root, array_merge(getenv(), $environment));
if (!is_resource($server)) { dropTestDatabase($db, $database); throw new RuntimeException('Servidor de testes indisponível.'); }
fclose($pipes[0]);

function browser(): CurlHandle
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => false, CURLOPT_COOKIEFILE => '', CURLOPT_TIMEOUT => 10]);
    return $curl;
}

function request(CurlHandle $curl, string $path, ?array $post = null): array
{
    curl_setopt($curl, CURLOPT_URL, 'http://127.0.0.1:18086' . $path);
    curl_setopt($curl, CURLOPT_POST, $post !== null);
    if ($post !== null) { curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post)); }
    $body = curl_exec($curl);
    return [(int) curl_getinfo($curl, CURLINFO_HTTP_CODE), $body === false ? '' : $body];
}

function token(CurlHandle $curl, string $path): string
{
    [$code, $html] = request($curl, $path);
    if ($code !== 200 || !preg_match('/name="csrf" value="([a-f0-9]+)"/', $html, $matches)) {
        throw new RuntimeException("Token ausente em $path (HTTP $code)");
    }
    return $matches[1];
}

function login(CurlHandle $curl, string $email, string $password): int
{
    $csrf = token($curl, '/login');
    return request($curl, '/login/autenticar', ['csrf' => $csrf, 'email' => $email, 'senha' => $password])[0];
}

try {
    $admin = browser();
    for ($i = 0; $i < 40; $i++) {
        if (request($admin, '/login')[0] === 200) { break; }
        usleep(100000);
    }
    check(login($admin, 'admin@rastreadorti.local', 'admin123') === 302, 'login admin');
    foreach (['home', 'home/equipamentos', 'home/equipamentos/novo', 'home/categorias', 'home/usuarios', 'home/emprestimos', 'home/manutencoes', 'home/conta', 'home/relatorios'] as $path) {
        [$code, $html] = request($admin, '/' . $path);
        check($code === 200 && !str_contains($html, 'Warning:') && !str_contains($html, 'Fatal error:'), 'renderização de ' . $path);
    }
    $csrf = token($admin, '/home/usuarios');
    $person = ['csrf' => $csrf, 'nome' => 'Pessoa HTTP', 'email' => 'http@example.test', 'perfil' => 'colaborador', 'ativo' => '1', 'senha' => 'HttpSenha12345!'];
    check(request($admin, '/home/usuarios/salvar', $person)[0] === 302, 'formulário cria usuário');
    $personId = (int) $db->query("SELECT id FROM usuarios WHERE email = 'http@example.test'")->fetchColumn();
    check($personId > 0, 'usuário persistido');
    $user = browser(); check(login($user, 'http@example.test', 'HttpSenha12345!') === 302, 'login colaborador');
    $userCsrf = token($user, '/home/conta');
    foreach (['home/usuarios', 'home/categorias', 'home/manutencoes', 'home/equipamentos/novo', 'home/relatorios', 'home/relatorios/csv'] as $path) {
        check(request($user, '/' . $path)[0] === 403, 'colaborador bloqueado em ' . $path);
    }
    foreach (['home/usuarios/salvar', 'home/categorias/salvar', 'home/emprestimos', 'home/emprestimos/devolver', 'home/manutencoes', 'home/manutencoes/concluir', 'home/equipamentos', 'home/equipamentos/atualizar', 'home/equipamentos/excluir'] as $path) {
        check(request($user, '/' . $path, ['csrf' => $userCsrf])[0] === 403, 'POST não autorizado em ' . $path);
    }
    $csrfStatus = request($admin, '/home/categorias/salvar', ['nome' => 'CSRF ausente'])[0];
    check($csrfStatus === 403, 'POST sem CSRF bloqueado (HTTP ' . $csrfStatus . ')');
    request($admin, '/home/categorias/salvar', ['csrf' => $csrf, 'nome' => 'Categoria HTTP', 'descricao' => 'Teste integrado']);
    $category = (int) $db->query("SELECT id FROM categorias WHERE nome = 'Categoria HTTP'")->fetchColumn();
    check($category > 0, 'formulário cria categoria');
    $equipment = ['csrf' => $csrf, 'nome' => 'Notebook HTTP', 'numero_serie' => '=TEST-HTTP', 'categoria_id' => $category, 'status' => 'disponivel'];
    check(request($admin, '/home/equipamentos', $equipment)[0] === 302, 'formulário cria equipamento');
    $equipmentId = (int) $db->query("SELECT id FROM equipamentos WHERE numero_serie = '=TEST-HTTP'")->fetchColumn();
    $loanInput = ['csrf' => $csrf, 'equipamento_id' => $equipmentId, 'colaborador_id' => $personId, 'previsao' => date('Y-m-d'), 'observacoes' => 'Teste'];
    $secondAdmin = browser(); login($secondAdmin, 'admin@rastreadorti.local', 'admin123');
    $secondLoan = $loanInput; $secondLoan['csrf'] = token($secondAdmin, '/home/emprestimos');
    // Duas sessões concorrentes: não há serialização pelo arquivo de sessão do PHP.
    $multi = curl_multi_init();
    foreach ([[$admin, $loanInput], [$secondAdmin, $secondLoan]] as [$client, $payload]) {
        curl_setopt($client, CURLOPT_URL, 'http://127.0.0.1:18086/home/emprestimos');
        curl_setopt($client, CURLOPT_POST, true);
        curl_setopt($client, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_multi_add_handle($multi, $client);
    }
    do {
        curl_multi_exec($multi, $running);
        if ($running) { curl_multi_select($multi, 0.1); }
    } while ($running);
    foreach ([$admin, $secondAdmin] as $client) { curl_multi_remove_handle($multi, $client); }
    curl_multi_close($multi);
    check((int) $db->query("SELECT COUNT(*) FROM emprestimos WHERE equipamento_id = $equipmentId AND status = 'ativo'")->fetchColumn() === 1, 'duas sessões concorrentes não duplicam empréstimo');
    $loanId = (int) $db->query("SELECT id FROM emprestimos WHERE equipamento_id = $equipmentId")->fetchColumn();
    check($loanId > 0, 'formulário cria empréstimo');
    check(str_contains(request($user, '/home/emprestimos')[1], 'Notebook HTTP'), 'colaborador vê próprio empréstimo');
    check(request($user, '/home/emprestimos/termo?id=' . $loanId)[0] === 200, 'termo do próprio empréstimo');
    check(request($user, '/home/emprestimos/termo?id=999999')[0] === 404, 'termo inexistente bloqueado');
    $other = $person; $other['email'] = 'outro@example.test';
    request($admin, '/home/usuarios/salvar', $other);
    $otherBrowser = browser(); login($otherBrowser, $other['email'], $other['senha']);
    check(request($otherBrowser, '/home/emprestimos/termo?id=' . $loanId)[0] === 404, 'termo de outro usuário bloqueado');
    check(!str_contains(request($otherBrowser, '/home/equipamentos')[1], 'Notebook HTTP'), 'inventário de outro usuário oculto');
    $late = $db->prepare('UPDATE emprestimos SET data_devolucao_prevista = ? WHERE id = ?');
    $late->execute([date('Y-m-d', strtotime('-1 day')), $loanId]);
    check(str_contains(request($user, '/home/emprestimos')[1], 'Atrasado'), 'empréstimo vencido sinalizado');
    request($admin, '/home/emprestimos/devolver', ['csrf' => $csrf, 'id' => $loanId, 'observacoes' => 'Devolvido']);
    check($db->query("SELECT status FROM equipamentos WHERE id = $equipmentId")->fetchColumn() === 'disponivel', 'devolução pelo formulário');
    $equipment['id'] = $equipmentId; $equipment['status'] = 'manutencao';
    request($admin, '/home/equipamentos/atualizar', $equipment);
    $maintenance = (int) $db->query("SELECT id FROM manutencoes WHERE equipamento_id = $equipmentId")->fetchColumn();
    check($maintenance > 0 && str_contains(request($admin, '/home/manutencoes')[1], 'Notebook HTTP'), 'equipamento marcado manutenção aparece na aba');
    request($admin, '/home/manutencoes/concluir', ['csrf' => $csrf, 'id' => $maintenance, 'fim' => date('Y-m-d'), 'custo' => '250.25', 'destino' => 'disponivel', 'descricao' => 'Reparo concluído']);
    check($db->query("SELECT status FROM equipamentos WHERE id = $equipmentId")->fetchColumn() === 'disponivel', 'conclusão pelo formulário');
    check(str_contains(request($admin, '/home/relatorios/csv')[1], "'=TEST-HTTP"), 'CSV neutraliza fórmulas');
    check(!str_contains(request($admin, '/home/equipamentos?q=NAOEXISTE')[1], 'Notebook HTTP'), 'busca filtra inventário');
    $oldSession = browser(); login($oldSession, 'http@example.test', 'HttpSenha12345!');
    request($user, '/home/conta/senha', ['csrf' => $userCsrf, 'senha_atual' => 'HttpSenha12345!', 'senha' => 'NovaHttpSenha123!', 'confirmacao' => 'NovaHttpSenha123!']);
    check(request($user, '/home')[0] === 200, 'sessão atual mantida após troca de senha');
    check(request($oldSession, '/home')[0] === 302, 'troca de senha revoga outras sessões');
    $newBrowser = browser();
    check(login($newBrowser, 'http@example.test', 'HttpSenha12345!') === 200, 'senha antiga recusada');
    check(login($newBrowser, 'http@example.test', 'NovaHttpSenha123!') === 302, 'senha nova aceita');
    $person['id'] = $personId; $person['ativo'] = '0'; $person['senha'] = '';
    request($admin, '/home/usuarios/salvar', $person);
    check(request($user, '/home')[0] === 302, 'usuário desativado perde sessão');
    check(request($admin, '/logout', ['csrf' => $csrf])[0] === 302 && request($admin, '/home')[0] === 302, 'logout bloqueia acesso posterior');
    if (getenv('TEST_APACHE')) {
        check(request($admin, '/public/index.php')[0] === 403, 'Apache bloqueia entrada direta em public');
        check(request($admin, '/config/Database.php')[0] === 403, 'Apache bloqueia config');
        check(request($admin, '/tests/http.php')[0] === 403, 'Apache bloqueia testes');
        check(request($admin, '/css/main.css')[0] === 200, 'Apache serve CSS após reescrita');
    }
    echo 'HTTP_OK' . PHP_EOL;
} finally {
    proc_terminate($server);
    proc_close($server);
    dropTestDatabase($db, $database);
}
