<?php

/** @var array<int, array<string, mixed>> $equipamentos */
/** @var bool $cadastroRealizado */
/** @var string $basePath */

$statusLabels = [
    'disponivel' => ['Disponível', 'text-bg-success'],
    'em_uso' => ['Em uso', 'text-bg-primary'],
    'manutencao' => ['Manutenção', 'text-bg-warning'],
    'baixado' => ['Baixado', 'text-bg-danger'],
];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Equipamentos</h1>
        <p class="text-muted mb-0">Ativos cadastrados no inventário.</p>
    </div>

    <a class="btn btn-success" href="<?= htmlspecialchars($basePath) ?>/home/equipamentos/novo">
        <i class="bi bi-plus-lg"></i> Novo equipamento
    </a>
</div>

<?php if ($cadastroRealizado): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Equipamento cadastrado com sucesso.
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
                    </tr>
                </thead>
                <tbody>
                    <?php if ($equipamentos === []): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
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
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
