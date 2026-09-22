<?php
$editing = null;
foreach ($categorias as $row) {
    if ((string) $row['id'] === (string) ($formOld['id'] ?? $_GET['editar'] ?? '')) { $editing = $row; break; }
}
$values = $formOld ?: ($editing ?? []);
?>
<h1>Categorias</h1>
<p class="text-muted">Organize o inventário por tipo de equipamento.</p>
<form method="post" action="<?= $e($basePath) ?>/home/categorias/salvar" class="card card-body mb-4">
    <?= $csrfField ?><input type="hidden" name="id" value="<?= $e($values['id'] ?? '') ?>">
    <h2 class="h5"><?= $editing ? 'Editar categoria' : 'Nova categoria' ?></h2>
    <div class="row g-3">
        <div class="col-md-4"><label for="nome" class="form-label">Nome</label><input id="nome" name="nome" class="form-control" maxlength="100" required value="<?= $e($values['nome'] ?? '') ?>"></div>
        <div class="col-md-8"><label for="descricao" class="form-label">Descrição</label><input id="descricao" name="descricao" class="form-control" maxlength="255" value="<?= $e($values['descricao'] ?? '') ?>"></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Salvar categoria</button> <a href="<?= $e($basePath) ?>/home/categorias" class="btn btn-outline-secondary">Limpar / cancelar</a></div>
</form>
<div class="table-responsive"><table class="table table-striped align-middle bg-white">
    <thead><tr><th>Nome</th><th>Descrição</th><th>Equipamentos</th><th>Ações</th></tr></thead>
    <tbody><?php foreach ($categorias as $row): ?><tr>
        <td><?= $e($row['nome']) ?></td><td><?= $e($row['descricao']) ?></td><td><?= (int) $row['total'] ?></td>
        <td><a class="btn btn-sm btn-outline-primary" href="<?= $e($basePath) ?>/home/categorias?editar=<?= (int) $row['id'] ?>">Editar</a>
        <form class="d-inline" method="post" action="<?= $e($basePath) ?>/home/categorias/excluir" onsubmit="return confirm('Excluir esta categoria?');">
            <?= $csrfField ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><button class="btn btn-sm btn-outline-danger" <?= $row['total'] ? 'disabled title="Categoria em uso"' : '' ?>>Excluir</button>
        </form></td>
    </tr><?php endforeach; ?><?php if (!$categorias): ?><tr><td colspan="4">Nenhuma categoria cadastrada.</td></tr><?php endif; ?></tbody>
</table></div>
