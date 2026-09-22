<?php

use Core\Csrf;

/** @var array<int, array<string, mixed>> $equipamentos */
/** @var string|null $mensagem */
/** @var bool $mensagemErro */
/** @var string $basePath */

$statusLabels = [
    'disponivel' => ['Disponível', 'text-bg-success'],
    'em_uso' => ['Em uso', 'text-bg-primary'],
    'manutencao' => ['Manutenção', 'text-bg-warning'],
    'baixado' => ['Baixado', 'text-bg-danger'],
];

$isAdmin = ($_SESSION['usuario_perfil'] ?? '') === 'admin';
$csrfToken = Csrf::token();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Equipamentos</h1>
        <p class="text-muted mb-0">Ativos cadastrados no inventário.</p>
    </div>

    <?php if ($isAdmin): ?>
        <a class="btn btn-success" href="<?= htmlspecialchars($basePath) ?>/home/equipamentos/novo">
            <i class="bi bi-plus-lg"></i> Novo equipamento
        </a>
    <?php endif; ?>
</div>

<?php $filterRoute = 'home/equipamentos'; require __DIR__ . '/components/filtro-equipamentos.php'; ?>
<?php if (($_SESSION['usuario_perfil'] ?? '') !== 'admin'): ?><p class="alert alert-info">Exibindo somente equipamentos com empréstimo ativo em seu nome.</p><?php endif; ?>
<?php if ($mensagem !== null): ?>
    <div class="alert alert-<?= $mensagemErro ? 'danger' : 'success' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensagem) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-3">Nome</th>
                        <th>Marca / Modelo</th>
                        <th>Categoria</th>
                        <th>Número de série</th>
                        <th>Status</th>
                        <th>Data de aquisição</th>
                        <th class="text-end pe-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($equipamentos === []): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                Nenhum equipamento cadastrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($equipamentos as $equipamento): ?>
                            <?php
                            [$statusText, $statusClass] = $statusLabels[$equipamento['status']]
                                ?? ['Desconhecido', 'text-bg-secondary'];
                            ?>
                            <tr>
                                <td class="ps-3 fw-semibold">
                                    <?= htmlspecialchars($equipamento['nome']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($equipamento['marca'] ?? '-') ?>
                                    /
                                    <?= htmlspecialchars($equipamento['modelo'] ?? '-') ?>
                                </td>
                                <td><?= htmlspecialchars($equipamento['categoria_nome']) ?></td>
                                <td><code><?= htmlspecialchars($equipamento['numero_serie']) ?></code></td>
                                <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                                <td>
                                    <?= $equipamento['data_aquisicao'] !== null
                                        ? htmlspecialchars(date('d/m/Y', strtotime($equipamento['data_aquisicao'])))
                                        : '-' ?>
                                </td>
                                <td class="text-end pe-3 text-nowrap">
                                    <?php if ($isAdmin): ?>
                                        <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($basePath) ?>/home/equipamentos/editar?id=<?= (int) $equipamento['id'] ?>" aria-label="Editar <?= htmlspecialchars($equipamento['nome']) ?>">
                                            <i class="bi bi-pencil"></i> Editar
                                        </a>
                                        <form class="d-inline" action="<?= htmlspecialchars($basePath) ?>/home/equipamentos/excluir" method="post" onsubmit="return confirm('Deseja realmente excluir este equipamento?');">
                                            <input type="hidden" name="id" value="<?= (int) $equipamento['id'] ?>">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i> Excluir</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
