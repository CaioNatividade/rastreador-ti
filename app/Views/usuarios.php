<?php
$editing = null;
foreach ($usuarios as $row) {
    if ((string) $row['id'] === (string) ($formOld['id'] ?? $_GET['editar'] ?? '')) { $editing = $row; break; }
}
$values = $formOld ?: ($editing ?? []);
?>
<h1>Usuários</h1>
<p class="text-muted">Cadastre responsáveis por empréstimos e controle o acesso. Desativar preserva o histórico e encerra o acesso na próxima requisição.</p>
<form method="post" action="<?= $e($basePath) ?>/home/usuarios/salvar" class="card card-body mb-4">
    <?= $csrfField ?><input type="hidden" name="id" value="<?= $e($values['id'] ?? '') ?>">
    <h2 class="h5"><?= $editing ? 'Editar usuário' : 'Novo usuário' ?></h2>
    <div class="row g-3">
        <div class="col-md-6"><label for="nome" class="form-label">Nome</label><input id="nome" name="nome" class="form-control" maxlength="150" required value="<?= $e($values['nome'] ?? '') ?>"></div>
        <div class="col-md-6"><label for="email" class="form-label">E-mail</label><input type="email" id="email" name="email" class="form-control" maxlength="150" required value="<?= $e($values['email'] ?? '') ?>"></div>
        <div class="col-md-4"><label for="perfil" class="form-label">Perfil</label><select id="perfil" name="perfil" class="form-select">
            <?php foreach (['colaborador' => 'Colaborador', 'admin' => 'Administrador'] as $key => $label): ?><option value="<?= $key ?>" <?= ($values['perfil'] ?? 'colaborador') === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?>
        </select></div>
        <div class="col-md-3"><label for="ativo" class="form-label">Situação</label><select id="ativo" name="ativo" class="form-select"><option value="1">Ativo</option><option value="0" <?= (string) ($values['ativo'] ?? '1') === '0' ? 'selected' : '' ?>>Desativado</option></select></div>
        <div class="col-md-5"><label for="senha" class="form-label"><?= $editing ? 'Nova senha (opcional)' : 'Senha inicial' ?></label><input type="password" id="senha" name="senha" class="form-control" minlength="10" maxlength="72" autocomplete="new-password" <?= $editing ? '' : 'required' ?>><div class="form-text">Mínimo de 10 caracteres. <?= $editing ? 'Deixe vazio para manter a senha atual.' : '' ?></div></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Salvar usuário</button> <a href="<?= $e($basePath) ?>/home/usuarios" class="btn btn-outline-secondary">Limpar / cancelar</a></div>
</form>
<div class="table-responsive"><table class="table table-striped align-middle bg-white"><thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Situação</th><th>Ações</th></tr></thead>
<tbody><?php foreach ($usuarios as $row): ?><tr><td><?= $e($row['nome']) ?></td><td><?= $e($row['email']) ?></td><td><?= $row['perfil'] === 'admin' ? 'Administrador' : 'Colaborador' ?></td><td><?= $row['ativo'] ? 'Ativo' : 'Desativado' ?></td><td><a class="btn btn-sm btn-outline-primary" href="<?= $e($basePath) ?>/home/usuarios?editar=<?= (int) $row['id'] ?>">Editar / desativar</a></td></tr><?php endforeach; ?></tbody></table></div>
