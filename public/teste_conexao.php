<?php

declare(strict_types=1);

use Config\Database;

require_once dirname(__DIR__) . '/vendor/autoload.php';

header('Content-Type: text/html; charset=UTF-8');

$tables = [
    'usuarios',
    'categorias',
    'equipamentos',
    'emprestimos',
    'manutencoes',
    'termos_responsabilidade',
];

$tableResults = [];
$categories = [];
$admin = false;
$connectionError = false;

try {
    $pdo = Database::getInstance();

    foreach ($tables as $table) {
        try {
            $statement = $pdo->query("SELECT COUNT(*) AS total FROM `{$table}`");
            $tableResults[$table] = (int) $statement->fetch()['total'];
        } catch (PDOException) {
            $tableResults[$table] = null;
        }
    }

    $categories = $pdo
        ->query('SELECT id, nome, descricao FROM categorias')
        ->fetchAll();

    $admin = $pdo
        ->query("SELECT nome, email, perfil FROM usuarios WHERE perfil = 'admin' LIMIT 1")
        ->fetch();
} catch (Throwable $error) {
    error_log($error->__toString());
    http_response_code(500);
    $connectionError = true;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Conexão</title>
</head>
<body>
    <h1>Teste de Conexão com o Banco de Dados (PDO)</h1>

    <?php if ($connectionError): ?>
        <h2 style="color: red;">Erro na conexão com o banco</h2>
        <p>Não foi possível concluir o teste. Consulte o log da aplicação.</p>
    <?php else: ?>
        <h2 style="color: green;">Conexão estabelecida com sucesso via PDO</h2>
        <h3>Verificação das tabelas</h3>

        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Tabela</th>
                    <th>Status</th>
                    <th>Registros</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tableResults as $table => $total): ?>
                    <tr>
                        <td><?= htmlspecialchars($table) ?></td>
                        <td style="color: <?= $total === null ? 'red' : 'green' ?>;">
                            <?= $total === null ? 'Não encontrada' : 'OK' ?>
                        </td>
                        <td><?= $total === null ? '-' : $total ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p>
            <strong>
                <?= count(array_filter($tableResults, static fn (?int $total): bool => $total !== null)) ?>
                de <?= count($tables) ?> tabelas encontradas e acessíveis.
            </strong>
        </p>

        <h3>Amostra de dados (categorias cadastradas)</h3>
        <?php if ($categories !== []): ?>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <strong><?= htmlspecialchars($category['nome']) ?></strong>
                        — <?= htmlspecialchars($category['descricao'] ?? '') ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nenhuma categoria cadastrada ainda.</p>
        <?php endif; ?>

        <h3>Usuário administrador</h3>
        <?php if ($admin): ?>
            <p>
                Encontrado: <strong><?= htmlspecialchars($admin['nome']) ?></strong>
                (<?= htmlspecialchars($admin['email']) ?>)
                — perfil: <?= htmlspecialchars($admin['perfil']) ?>
            </p>
        <?php else: ?>
            <p>Nenhum usuário administrador encontrado.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
