<?php

use Core\Csrf;

/** @var array<int, array{id: int, nome: string}> $categorias */
/** @var array<string, string> $errors */
/** @var array<string, string> $old */
/** @var string $basePath */
/** @var int|null $equipmentId */

$value = static fn (string $field): string => htmlspecialchars($old[$field] ?? '');
$invalidClass = static fn (string $field): string => isset($errors[$field]) ? ' is-invalid' : '';
$csrfToken = Csrf::token();
?>
<div class="mb-4">
    <h1 class="mb-1"><?= $equipmentId === null ? 'Novo equipamento' : 'Editar equipamento' ?></h1>
    <p class="text-muted mb-0">Preencha os dados do ativo no inventário.</p>
</div>

<?php if ($errors !== []): ?>
    <div class="alert alert-danger" role="alert">
        Revise os campos destacados antes de salvar.
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="<?= htmlspecialchars($basePath) ?>/home/equipamentos<?= $equipmentId === null ? '' : '/atualizar' ?>" method="post" novalidate>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <?php if ($equipmentId !== null): ?>
                <input type="hidden" name="id" value="<?= $equipmentId ?>">
            <?php endif; ?>
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

                <div class="col-md-3">
                    <label for="status" class="form-label">Status *</label>
                    <p class="small text-muted">Em uso é controlado por Empréstimos. Marcar Manutenção abre uma ficha; conclua-a na aba Manutenções.</p>
                    <select class="form-select<?= $invalidClass('status') ?>" id="status" name="status" required>
                        <?php foreach (['disponivel' => 'Disponível', 'em_uso' => 'Em uso', 'manutencao' => 'Manutenção', 'baixado' => 'Baixado'] as $statusValue => $statusLabel): ?>
                            <option value="<?= $statusValue ?>" <?= ($old['status'] ?? 'disponivel') === $statusValue ? 'selected' : '' ?> <?= $statusValue === 'em_uso' && ($old['status'] ?? '') !== 'em_uso' ? 'disabled' : '' ?>><?= $statusLabel ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['status'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['status']) ?></div><?php endif; ?>
                </div>

                <div class="col-12">
                    <label for="observacoes" class="form-label">Observações</label>
                    <textarea
                        class="form-control<?= $invalidClass('observacoes') ?>"
                        id="observacoes"
                        name="observacoes"
                        rows="3" maxlength="5000"
                    ><?= $value('observacoes') ?></textarea>
                    <?php if (isset($errors['observacoes'])): ?><div class="invalid-feedback"><?= htmlspecialchars($errors['observacoes']) ?></div><?php endif; ?>
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
