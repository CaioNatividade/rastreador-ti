<h1>Relatório do inventário</h1>
<p>Gerado em <?= date('d/m/Y H:i') ?> · <?= count($equipamentos) ?> equipamento(s) no filtro.</p>
<?php $filterRoute = 'home/relatorios'; require __DIR__ . '/components/filtro-equipamentos.php'; ?>
<div class="d-print-none mb-4"><button class="btn btn-primary" onclick="window.print()">Imprimir / salvar PDF</button> <a class="btn btn-outline-primary" href="<?= $e($basePath . '/home/relatorios/csv?' . http_build_query(['q' => $query, 'status' => $filterStatus])) ?>">Exportar CSV</a></div>
<div class="table-responsive"><table class="table table-striped bg-white"><thead><tr><th>Equipamento</th><th>Série</th><th>Categoria</th><th>Status</th><th>Aquisição</th></tr></thead><tbody>
<?php $labels = ['disponivel' => 'Disponível', 'em_uso' => 'Em uso', 'manutencao' => 'Manutenção', 'baixado' => 'Baixado']; ?>
<?php foreach ($equipamentos as $row): ?><tr><td><?= $e($row['nome']) ?></td><td><?= $e($row['numero_serie']) ?></td><td><?= $e($row['categoria_nome']) ?></td><td><?= $e($labels[$row['status']] ?? $row['status']) ?></td><td><?= $row['data_aquisicao'] ? $e(date('d/m/Y', strtotime($row['data_aquisicao']))) : '—' ?></td></tr><?php endforeach; ?>
<?php if (!$equipamentos): ?><tr><td colspan="5">Nenhum equipamento encontrado.</td></tr><?php endif; ?></tbody></table></div>
