<h1>Manutenções</h1>
<p class="text-muted">Abrir uma manutenção bloqueia o equipamento para empréstimos. Concluir permite disponibilizá-lo ou dar baixa.</p>
<?php $pending = array_filter($equipamentos, static fn (array $row): bool => $row['status'] === 'manutencao'); ?>
<?php if ($pending): ?><div class="alert alert-warning"><strong>Equipamentos já marcados como manutenção, sem ficha:</strong><ul class="mb-0"><?php foreach ($pending as $row): ?><li><?= $e($row['nome']) ?> (<?= $e($row['numero_serie']) ?>) — selecione abaixo para registrar os detalhes.</li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" action="<?= $e($basePath) ?>/home/manutencoes" class="card card-body mb-4">
    <?= $csrfField ?><h2 class="h5">Abrir manutenção</h2>
    <div class="row g-3">
        <div class="col-md-8"><label for="equipamento_id" class="form-label">Equipamento</label><select id="equipamento_id" name="equipamento_id" class="form-select" required><option value="">Selecione...</option><?php foreach ($equipamentos as $row): ?><option value="<?= (int) $row['id'] ?>" <?= (string) ($formOld['equipamento_id'] ?? '') === (string) $row['id'] ? 'selected' : '' ?>><?= $e($row['nome'] . ' — ' . $row['numero_serie']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label for="inicio" class="form-label">Data de início</label><input type="date" id="inicio" name="inicio" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= $e($formOld['inicio'] ?? date('Y-m-d')) ?>" required></div>
        <div class="col-12"><label for="descricao" class="form-label">Problema / serviço</label><textarea id="descricao" name="descricao" class="form-control" maxlength="5000" required><?= $e(isset($formOld['equipamento_id']) ? ($formOld['descricao'] ?? '') : '') ?></textarea></div>
    </div><div class="mt-3"><button class="btn btn-primary" <?= !$equipamentos ? 'disabled' : '' ?>>Abrir manutenção</button></div>
    <?php if (!$equipamentos): ?><p class="mt-2 mb-0">Nenhum equipamento disponível. Registre a devolução dos emprestados antes de abrir manutenção.</p><?php endif; ?>
</form>
<h2 class="h4">Em andamento e histórico</h2>
<?php if (!$manutencoes): ?><p class="alert alert-light">Nenhuma manutenção registrada.</p><?php endif; ?>
<?php foreach ($manutencoes as $row): $retry = (string) ($formOld['id'] ?? '') === (string) $row['id'] ? $formOld : []; ?>
<article class="card card-body mb-3">
    <h3 class="h5">#<?= (int) $row['id'] ?> — <?= $e($row['equipamento']) ?> <small class="text-muted"><?= $e($row['numero_serie']) ?></small></h3>
    <p><span class="badge <?= $row['status'] === 'em_andamento' ? 'text-bg-warning' : 'text-bg-success' ?>"><?= $row['status'] === 'em_andamento' ? 'Em andamento' : 'Concluída' ?></span> · Início: <?= $e(date('d/m/Y', strtotime($row['data_inicio']))) ?></p>
    <p class="text-break" style="white-space: pre-wrap;"><?= $e($row['descricao']) ?></p>
    <?php if ($row['status'] === 'em_andamento'): ?>
    <details <?= $retry ? 'open' : '' ?>><summary class="text-primary">Concluir manutenção</summary>
    <form class="mt-3" method="post" action="<?= $e($basePath) ?>/home/manutencoes/concluir">
        <?= $csrfField ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label" for="fim-<?= (int) $row['id'] ?>">Conclusão</label><input class="form-control" type="date" id="fim-<?= (int) $row['id'] ?>" name="fim" min="<?= $e($row['data_inicio']) ?>" max="<?= date('Y-m-d') ?>" value="<?= $e($retry['fim'] ?? date('Y-m-d')) ?>" required></div>
            <div class="col-md-4"><label class="form-label" for="custo-<?= (int) $row['id'] ?>">Custo (R$, opcional)</label><input class="form-control" type="number" step="0.01" min="0" max="99999999.99" id="custo-<?= (int) $row['id'] ?>" name="custo" value="<?= $e($retry['custo'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label" for="destino-<?= (int) $row['id'] ?>">Após a manutenção</label><select class="form-select" id="destino-<?= (int) $row['id'] ?>" name="destino"><option value="disponivel">Disponível</option><option value="baixado" <?= ($retry['destino'] ?? '') === 'baixado' ? 'selected' : '' ?>>Baixado (sem recuperação)</option></select></div>
            <div class="col-12"><label class="form-label" for="desc-<?= (int) $row['id'] ?>">Descrição completa do problema e serviço realizado</label><textarea class="form-control" id="desc-<?= (int) $row['id'] ?>" name="descricao" maxlength="5000" required><?= $e($retry['descricao'] ?? $row['descricao']) ?></textarea></div>
        </div><button class="btn btn-success mt-3">Concluir manutenção</button>
    </form></details>
    <?php else: ?><p class="mb-0">Conclusão: <?= $e(date('d/m/Y', strtotime($row['data_fim']))) ?> · Custo: <?= $row['custo'] === null ? 'Não informado' : 'R$ ' . number_format((float) $row['custo'], 2, ',', '.') ?></p><?php endif; ?>
</article>
<?php endforeach; ?>
