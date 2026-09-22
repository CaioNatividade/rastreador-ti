<h1><?= $admin ? 'Empréstimos' : 'Meus empréstimos' ?></h1>
<p class="text-muted">Acompanhe as entregas, as devoluções e os termos de responsabilidade.</p>
<?php if ($admin): ?>
<?php if ($semResponsavel): ?><div class="alert alert-warning">Equipamentos antigos marcados como Em uso sem empréstimo registrado:
    <ul class="mb-0"><?php foreach ($semResponsavel as $row): ?><li><?= $e($row['nome'] . ' — ' . $row['numero_serie']) ?>. <a href="<?= $e($basePath) ?>/home/equipamentos/editar?id=<?= (int) $row['id'] ?>">Edite para Disponível</a> e registre a entrega ao responsável abaixo.</li><?php endforeach; ?></ul>
</div><?php endif; ?>
<form method="post" action="<?= $e($basePath) ?>/home/emprestimos" class="card card-body mb-4">
    <?= $csrfField ?><h2 class="h5">Registrar empréstimo</h2>
    <div class="row g-3">
        <div class="col-md-6"><label for="equipamento_id" class="form-label">Equipamento disponível</label><select id="equipamento_id" name="equipamento_id" class="form-select" required><option value="">Selecione...</option><?php foreach ($equipamentos as $row): ?><option value="<?= (int) $row['id'] ?>" <?= (string) ($formOld['equipamento_id'] ?? '') === (string) $row['id'] ? 'selected' : '' ?>><?= $e($row['nome'] . ' — ' . $row['numero_serie']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label for="colaborador_id" class="form-label">Colaborador responsável</label><select id="colaborador_id" name="colaborador_id" class="form-select" required><option value="">Selecione...</option><?php foreach ($colaboradores as $row): ?><option value="<?= (int) $row['id'] ?>" <?= (string) ($formOld['colaborador_id'] ?? '') === (string) $row['id'] ? 'selected' : '' ?>><?= $e($row['nome'] . ' — ' . $row['email']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-4"><label for="previsao" class="form-label">Devolução prevista</label><input type="date" id="previsao" name="previsao" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= $e($formOld['previsao'] ?? '') ?>" required></div>
        <div class="col-md-8"><label for="observacoes" class="form-label">Condições de entrega / observações</label><textarea id="observacoes" name="observacoes" class="form-control" maxlength="5000"><?= $e(isset($formOld['equipamento_id']) ? ($formOld['observacoes'] ?? '') : '') ?></textarea></div>
    </div><div class="mt-3"><button class="btn btn-primary" <?= !$equipamentos || !$colaboradores ? 'disabled' : '' ?>>Registrar entrega</button></div>
    <?php if (!$colaboradores): ?><p class="mt-2 mb-0">Cadastre um colaborador ativo em <a href="<?= $e($basePath) ?>/home/usuarios">Usuários</a>.</p><?php endif; ?>
    <?php if (!$equipamentos): ?><p class="mt-2 mb-0">Nenhum equipamento disponível para empréstimo.</p><?php endif; ?>
</form>
<?php endif; ?>
<?php if (!$emprestimos): ?><p class="alert alert-light">Nenhum empréstimo registrado.</p><?php endif; ?>
<?php foreach ($emprestimos as $row):
    $late = $row['status'] === 'ativo' && $row['data_devolucao_prevista'] !== null && $row['data_devolucao_prevista'] < date('Y-m-d');
    $retry = (string) ($formOld['id'] ?? '') === (string) $row['id'] ? $formOld : [];
?>
<article class="card card-body mb-3">
    <h2 class="h5">#<?= (int) $row['id'] ?> — <?= $e($row['equipamento']) ?> <small class="text-muted"><?= $e($row['numero_serie']) ?></small></h2>
    <p class="mb-2">Responsável: <?= $e($row['colaborador']) ?> · <span class="badge <?= $late ? 'text-bg-danger' : ($row['status'] === 'ativo' ? 'text-bg-primary' : 'text-bg-success') ?>"><?= $late ? 'Atrasado' : ($row['status'] === 'ativo' ? 'Ativo' : 'Devolvido') ?></span></p>
    <p>Entrega: <?= $e(date('d/m/Y H:i', strtotime($row['data_entrega']))) ?> · Previsão: <?= $row['data_devolucao_prevista'] ? $e(date('d/m/Y', strtotime($row['data_devolucao_prevista']))) : 'Não informada' ?></p>
    <?php if ($row['observacoes_entrega']): ?><p class="text-break">Entrega: <?= $e($row['observacoes_entrega']) ?></p><?php endif; ?>
    <?php if ($row['data_devolucao']): ?><p>Devolução: <?= $e(date('d/m/Y H:i', strtotime($row['data_devolucao']))) ?> — <?= $e($row['observacoes_devolucao']) ?></p><?php endif; ?>
    <div><a class="btn btn-sm btn-outline-primary" href="<?= $e($basePath) ?>/home/emprestimos/termo?id=<?= (int) $row['id'] ?>">Termo / imprimir</a></div>
    <?php if ($admin && $row['status'] === 'ativo'): ?><details class="mt-3" <?= $retry ? 'open' : '' ?>><summary class="text-primary">Registrar devolução</summary><form method="post" action="<?= $e($basePath) ?>/home/emprestimos/devolver" class="mt-2" onsubmit="return confirm('Confirmar o recebimento do equipamento e encerrar o empréstimo?');">
        <?= $csrfField ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
        <label for="devolucao-<?= (int) $row['id'] ?>" class="form-label">Condições de devolução</label><textarea class="form-control" id="devolucao-<?= (int) $row['id'] ?>" name="observacoes" maxlength="5000"><?= $e($retry['observacoes'] ?? '') ?></textarea><button class="btn btn-success mt-2">Confirmar devolução</button>
    </form></details><?php endif; ?>
</article>
<?php endforeach; ?>
