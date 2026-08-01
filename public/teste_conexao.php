<?php
require_once '../config/Database.php';

// Lista de todas as tabelas que devem existir no banco, conforme database/rastreio_ti.sql
$tabelas = [
    'usuarios',
    'categorias',
    'equipamentos',
    'emprestimos',
    'manutencoes',
    'termos_responsabilidade',
];

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'><head><meta charset='UTF-8'><title>Teste de Conexão</title></head><body>";
echo "<h1>Teste de Conexão com o Banco de Dados (PDO)</h1>";

try {
    $pdo = Database::getInstance();

    echo "<h2 style='color: green;'>✅ Conexão estabelecida com sucesso via PDO</h2>";

    // ---------------------------------------------------------------
    // 1. Verifica se cada tabela esperada existe e conta os registros
    // ---------------------------------------------------------------
    echo "<h3>Verificação das tabelas</h3>";
    echo "<table border='1' cellpadding='8' cellspacing='0'>";
    echo "<tr><th>Tabela</th><th>Status</th><th>Registros</th></tr>";

    $tabelasOk = 0;

    foreach ($tabelas as $tabela) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) AS total FROM `$tabela`");
            $total = $stmt->fetch()['total'];

            echo "<tr>
                    <td>$tabela</td>
                    <td style='color: green;'>OK ✅</td>
                    <td>$total</td>
                  </tr>";
            $tabelasOk++;
        } catch (PDOException $e) {
            echo "<tr>
                    <td>$tabela</td>
                    <td style='color: red;'>Não encontrada ❌</td>
                    <td>-</td>
                  </tr>";
        }
    }

    echo "</table>";
    echo "<p><strong>$tabelasOk de " . count($tabelas) . " tabelas encontradas e acessíveis.</strong></p>";

    // ---------------------------------------------------------------
    // 2. Mostra uma amostra de dados reais (categorias cadastradas)
    // ---------------------------------------------------------------
    echo "<h3>Amostra de dados (categorias cadastradas)</h3>";
    $stmt = $pdo->query('SELECT id, nome, descricao FROM categorias');
    $categorias = $stmt->fetchAll();

    if (count($categorias) > 0) {
        echo "<ul>";
        foreach ($categorias as $categoria) {
            echo "<li><strong>" . htmlspecialchars($categoria['nome']) . "</strong>"
               . " — " . htmlspecialchars($categoria['descricao'] ?? '') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nenhuma categoria cadastrada ainda.</p>";
    }

    // ---------------------------------------------------------------
    // 3. Confirma existência do usuário admin (sem expor dados sensíveis)
    // ---------------------------------------------------------------
    echo "<h3>Usuário administrador</h3>";
    $stmt = $pdo->query("SELECT nome, email, perfil FROM usuarios WHERE perfil = 'admin' LIMIT 1");
    $admin = $stmt->fetch();

    if ($admin) {
        echo "<p>✅ Encontrado: <strong>" . htmlspecialchars($admin['nome']) . "</strong>"
           . " (" . htmlspecialchars($admin['email']) . ") - perfil: " . htmlspecialchars($admin['perfil']) . "</p>";
    } else {
        echo "<p>⚠️ Nenhum usuário administrador encontrado.</p>";
    }

} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Erro na conexão com o banco</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Verifique se o MySQL está ligado no XAMPP e se o banco <code>rastreio_ti</code> foi importado corretamente.</p>";
}

echo "</body></html>";
