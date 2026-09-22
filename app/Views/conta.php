<h1>Minha conta</h1>
<p>Olá, <?= $e($_SESSION['usuario_nome']) ?>. Altere sua senha sem precisar acessar o banco de dados.</p>
<form method="post" action="<?= $e($basePath) ?>/home/conta/senha" class="card card-body" style="max-width: 600px;">
    <?= $csrfField ?>
    <label for="senha_atual" class="form-label">Senha atual</label><input type="password" id="senha_atual" name="senha_atual" class="form-control mb-3" autocomplete="current-password" required>
    <label for="senha" class="form-label">Nova senha (mínimo 10 caracteres)</label><input type="password" id="senha" name="senha" class="form-control mb-3" autocomplete="new-password" minlength="10" maxlength="72" required>
    <label for="confirmacao" class="form-label">Repita a nova senha</label><input type="password" id="confirmacao" name="confirmacao" class="form-control mb-3" autocomplete="new-password" minlength="10" maxlength="72" required>
    <p class="text-muted">As outras sessões serão encerradas na próxima requisição.</p>
    <button class="btn btn-primary">Alterar senha</button>
</form>
