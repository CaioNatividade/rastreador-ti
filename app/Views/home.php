<?php

/** @var array<int, array<string, mixed>> $equipamentosRecentes */
/** @var array{total: int, disponiveis: int, em_uso: int, manutencao: int} $resumo */

$statusLabels = [
    'disponivel' => ['Disponível', 'text-bg-success'],
    'em_uso' => ['Em uso', 'text-bg-primary'],
    'manutencao' => ['Manutenção', 'text-bg-warning'],
    'baixado' => ['Baixado', 'text-bg-danger'],
];
?>
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="mb-1">Dashboard</h1>
        <p class="text-muted mb-0">Visão geral do inventário de equipamentos.</p>
    </div>

    <div class="row g-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body border-start border-5 border-primary rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase">Total de ativos</h6>
                            <span class="fs-2 fw-semibold"><?= $resumo['total'] ?></span>
                        </div>
                        <i class="bi bi-pc-display fs-1 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body border-start border-5 border-success rounded">
                    <h6 class="text-muted text-uppercase">Disponíveis</h6>
                    <span class="fs-2 fw-semibold"><?= $resumo['disponiveis'] ?></span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body border-start border-5 border-primary rounded">
                    <h6 class="text-muted text-uppercase">Em uso</h6>
                    <span class="fs-2 fw-semibold"><?= $resumo['em_uso'] ?></span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body border-start border-5 border-warning rounded">
                    <h6 class="text-muted text-uppercase">Em manutenção</h6>
                    <span class="fs-2 fw-semibold"><?= $resumo['manutencao'] ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-5">
        <div class="card-header bg-white py-3">
            <h2 class="h5 mb-0">Equipamentos cadastrados recentemente</h2>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="ps-3">Equipamento</th>
                            <th>Categoria</th>
                            <th>Número de série</th>
                            <th>Status</th>
                            <th>Data de cadastro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($equipamentosRecentes === []): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    Nenhum equipamento cadastrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($equipamentosRecentes as $equipamento): ?>
                                <?php
                                [$statusText, $statusClass] = $statusLabels[$equipamento['status']]
                                    ?? ['Desconhecido', 'text-bg-secondary'];
                                $nomeModelo = trim(
                                    $equipamento['nome']
                                    . ($equipamento['modelo'] ? ' — ' . $equipamento['modelo'] : '')
                                );
                                ?>
                                <tr>
                                    <td class="ps-3 fw-semibold"><?= htmlspecialchars($nomeModelo) ?></td>
                                    <td><?= htmlspecialchars($equipamento['categoria_nome']) ?></td>
                                    <td><code><?= htmlspecialchars($equipamento['numero_serie']) ?></code></td>
                                    <td><span class="badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($equipamento['criado_em']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
