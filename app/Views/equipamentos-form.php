<?php

/** @var array<int, array{id: int, nome: string}> $categorias */
/** @var array<string, string> $errors */
/** @var array<string, string> $old */
/** @var string $basePath */

$value = static fn (string $field): string => htmlspecialchars($old[$field] ?? '');
$invalidClass = static fn (string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
?>
<div class="mb-4">
    <h1 class="mb-1">Novo equipamento</h1>
    <p class="text-muted mb-0">Preencha os dados para adicionar um ativo ao inventário.</p>
</div>

<?php if ($errors !== []): ?>
    <div class="alert alert-danger" role="alert">
        Revise os campos destacados antes de salvar.
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="<?= htmlspecialchars($basePath) ?>/home/equipamentos" method="post" novalidate>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nome" class="form-label">Nome do equipamento *</label>
                    <input
                        type="text"
                        class="form-control<?= $invalidClass('nome') ?>"
                        id="nome"
                        name="nome"
                        maxlength="150"
                        value="<?= $value('nome') ?>"
                        required
                    >
                    <?php if (isset($errors['nome'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['nome']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label for="marca" class="form-label">Marca</label>
                    <input
                        type="text"
                        class="form-control<?= $invalidClass('marca') ?>"
                        id="marca"
                        name="marca"
                        maxlength="100"
                        value="<?= $value('marca') ?>"
                    >
                    <?php if (isset($errors['marca'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['marca']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label for="modelo" class="form-label">Modelo</label>
                    <input
                        type="text"
                        class="form-control<?= $invalidClass('modelo') ?>"
                        id="modelo"
                        name="modelo"
                        maxlength="100"
                        value="<?= $value('modelo') ?>"
                    >
                    <?php if (isset($errors['modelo'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['modelo']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label for="numero_serie" class="form-label">Número de série *</label>
                    <input
                        type="text"
                        class="form-control<?= $invalidClass('numero_serie') ?>"
                        id="numero_serie"
                        name="numero_serie"
                        maxlength="100"
                        value="<?= $value('numero_serie') ?>"
                        required
                    >
                    <?php if (isset($errors['numero_serie'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['numero_serie']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label for="categoria_id" class="form-label">Categoria *</label>
                    <select
                        class="form-select<?= $invalidClass('categoria_id') ?>"
                        id="categoria_id"
                        name="categoria_id"
                        required
                    >
                        <option value="">Selecione...</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option
                                value="<?= (int) $categoria['id'] ?>"
                                <?= ($old['categoria_id'] ?? '') === (string) $categoria['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($categoria['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['categoria_id'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['categoria_id']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-3">
                    <label for="data_aquisicao" class="form-label">Data de aquisição</label>
                    <input
                        type="date"
                        class="form-control<?= $invalidClass('data_aquisicao') ?>"
                        id="data_aquisicao"
                        name="data_aquisicao"
                        value="<?= $value('data_aquisicao') ?>"
                    >
                    <?php if (isset($errors['data_aquisicao'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['data_aquisicao']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-12">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea
                        class="form-control"
                        id="observacoes"
                        name="observacoes"
                        rows="3"
                    ><?= $value('observacoes') ?></textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Salvar equipamento
                </button>
                <a class="btn btn-outline-secondary" href="<?= htmlspecialchars($basePath) ?>/home/equipamentos">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
