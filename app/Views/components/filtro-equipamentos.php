<?php $query = is_string($_GET['q'] ?? null) ? $_GET['q'] : ''; $filterStatus = is_string($_GET['status'] ?? null) ? $_GET['status'] : ''; ?>
<form method="get" class="row g-2 mb-4 d-print-none" action="<?= $e($basePath . '/' . $filterRoute) ?>">
    <div class="col-md-6"><label for="busca" class="form-label">Nome, série ou categoria</label><input id="busca" class="form-control" name="q" value="<?= $e($query) ?>" maxlength="150"></div>
    <div class="col-md-4"><label for="filtro-status" class="form-label">Status</label><select id="filtro-status" class="form-select" name="status"><option value="">Todos</option><?php foreach (['disponivel' => 'Disponível', 'em_uso' => 'Em uso', 'manutencao' => 'Manutenção', 'baixado' => 'Baixado'] as $key => $label): ?><option value="<?= $key ?>" <?= $filterStatus === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary">Filtrar</button></div>
</form>
