<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use App\Services\AtivosService;
use App\Services\CadastrosService;
use App\Models\EquipamentosModel;
use App\Models\EquipamentosRepository;
use App\Models\UsuarioRepository;

[$db, $database] = testDatabase();
try {
    $assets = new AtivosService($db);
    $records = new CadastrosService($db);
    $users = new UsuarioRepository();
    $repo = new EquipamentosRepository($db);
    $today = date('Y-m-d');
    $input = ['nome' => 'Colaborador teste', 'email' => 'colaborador@example.test', 'perfil' => 'colaborador', 'ativo' => '1', 'senha' => 'TesteSeguro123!'];
    $records->saveUser(null, $input, 1);
    $user = (int) $db->query("SELECT id FROM usuarios WHERE email = 'colaborador@example.test'")->fetchColumn();
    check($users->autenticar($input['email'], $input['senha']) !== null, 'criação de usuário com senha verificada');
    rejected(fn () => $records->saveUser(null, $input, 1), 'e-mail duplicado');
    rejected(fn () => $records->saveUser(1, array_replace($input, ['email' => 'admin@rastreadorti.local']), 1), 'remoção do próprio perfil admin');
    $records->saveCategory(null, 'Categoria de teste', 'Descrição');
    $category = (int) $db->lastInsertId();
    $records->saveCategory($category, 'Categoria editada', 'Outra descrição');
    rejected(fn () => $records->saveCategory(null, 'Categoria editada', ''), 'categoria duplicada');

    $equipment = new EquipamentosModel();
    $equipment->nome = 'Notebook teste'; $equipment->numeroSerie = 'SERIAL-TESTE'; $equipment->categoriaId = $category;
    $id = $assets->saveEquipment($equipment);
    check($repo->find($id)['status'] === 'disponivel', 'cadastro de equipamento disponível');
    rejected(fn () => $records->deleteCategory($category), 'exclusão de categoria vinculada');
    $loan = $assets->lend($id, $user, 1, $today, 'Em perfeito estado');
    check($repo->find($id)['status'] === 'em_uso', 'empréstimo atualiza status');
    check(count($repo->select($user)) === 1 && count($repo->select(9999)) === 0, 'escopo de equipamentos por colaborador');
    check(count($assets->loans($user)) === 1 && count($assets->loans(9999)) === 0, 'escopo de empréstimos por colaborador');
    rejected(fn () => $assets->lend($id, $user, 1, $today, ''), 'empréstimo duplicado');
    rejected(fn () => $assets->startMaintenance($id, 'Problema', $today), 'manutenção durante empréstimo');
    $equipment->status = 'disponivel';
    rejected(fn () => $assets->saveEquipment($equipment), 'alteração manual de status de equipamento emprestado');
    check($repo->find($id)['status'] === 'em_uso', 'rollback preserva status');
    $assets->returnLoan($loan, 'Recebido sem avarias');
    check($repo->find($id)['status'] === 'disponivel' && count($repo->select($user)) === 0, 'devolução libera equipamento');
    rejected(fn () => $assets->returnLoan($loan, ''), 'devolução repetida');
    rejected(fn () => $assets->deleteEquipment($id), 'histórico de empréstimo preservado');
    rejected(fn () => $assets->lend($id, $user, 1, '2026-02-30', ''), 'data de previsão inválida');

    $equipment->status = 'manutencao';
    $assets->saveEquipment($equipment);
    $maintenance = $db->query("SELECT * FROM manutencoes WHERE equipamento_id = $id")->fetch();
    check($maintenance['status'] === 'em_andamento', 'tag manutenção cria ficha automaticamente');
    $assets->saveEquipment($equipment);
    check((int) $db->query("SELECT COUNT(*) FROM manutencoes WHERE equipamento_id = $id")->fetchColumn() === 1, 'editar equipamento não duplica manutenção');
    rejected(fn () => $assets->lend($id, $user, 1, $today, ''), 'empréstimo durante manutenção');
    $equipment->status = 'baixado';
    rejected(fn () => $assets->saveEquipment($equipment), 'baixa manual não ignora manutenção ativa');
    rejected(fn () => $assets->finishMaintenance((int) $maintenance['id'], '2000-01-01', '5', 'disponivel', 'Serviço'), 'conclusão anterior ao início');
    rejected(fn () => $assets->finishMaintenance((int) $maintenance['id'], $today, '-5', 'disponivel', 'Serviço'), 'custo negativo');
    $assets->finishMaintenance((int) $maintenance['id'], $today, '125,50', 'disponivel', 'Substituição de bateria');
    check($repo->find($id)['status'] === 'disponivel', 'conclusão de manutenção libera equipamento');
    check($db->query('SELECT custo FROM manutencoes LIMIT 1')->fetchColumn() === '125.50', 'custo decimal preservado');
    rejected(fn () => $assets->finishMaintenance((int) $maintenance['id'], $today, '0', 'disponivel', 'Serviço'), 'conclusão duplicada');
    $assets->startMaintenance($id, 'Falha irrecuperável', $today);
    $last = (int) $db->query("SELECT MAX(id) FROM manutencoes WHERE equipamento_id = $id")->fetchColumn();
    $assets->finishMaintenance($last, $today, '', 'baixado', 'Sem possibilidade de recuperação');
    check($repo->find($id)['status'] === 'baixado', 'baixa ao concluir manutenção');
    rejected(fn () => $assets->lend($id, $user, 1, $today, ''), 'empréstimo de equipamento baixado');

    $fresh = new EquipamentosModel(); $fresh->nome = 'Novo ativo'; $fresh->numeroSerie = 'SEM-HISTORICO'; $fresh->categoriaId = $category;
    $fresh->status = 'em_uso';
    rejected(fn () => $assets->saveEquipment($fresh), 'em uso exige um empréstimo');
    $fresh->status = 'disponivel'; $freshId = $assets->saveEquipment($fresh);
    $assets->deleteEquipment($freshId);
    check($repo->find($freshId) === null, 'exclusão permitida sem histórico');

    $records->changePassword($user, 'TesteSeguro123!', 'OutraSenhaSegura123!', 'OutraSenhaSegura123!');
    check($users->autenticar($input['email'], 'TesteSeguro123!') === null && $users->autenticar($input['email'], 'OutraSenhaSegura123!') !== null, 'troca de senha invalida a anterior');
    rejected(fn () => $records->changePassword($user, 'errada', 'TerceiraSenha123!', 'TerceiraSenha123!'), 'troca com senha atual incorreta');
    $input['ativo'] = '0'; $input['senha'] = '';
    $records->saveUser($user, $input, 1);
    check($users->findActive($user) === null, 'desativação remove acesso');
    echo 'INTEGRATION_OK' . PHP_EOL;
} finally {
    dropTestDatabase($db, $database);
}
