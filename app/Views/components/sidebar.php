<?php

use Core\Csrf;

/** @var string $basePath */
$url = static fn(string $route): string => htmlspecialchars($basePath . '/' . $route);
$isAdmin = ($_SESSION['usuario_perfil'] ?? '') === 'admin';
$csrfToken = Csrf::token();
?>
<nav class="sidebar d-flex flex-column align-items-center py-4 bg-primary text-dark">
    <h4 class="text-white fs-2">Rastreador TI</h4>
    <hr>

    <ul class="nav flex-column gap-3">
        <li class="nav-item">
            <a class="nav-link text-white fs-4 rounded" href="<?= $url('home') ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white fs-4 rounded" href="<?= $url('home/equipamentos') ?>">
                <i class="bi bi-pc-display"></i> Equipamentos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white fs-4 rounded" href="<?= $url('home/emprestimos') ?>">
                <i class="bi bi-arrow-left-right"></i> Empréstimos
            </a>
        </li>
        <?php if ($isAdmin): ?>
        <li class="nav-item">
            <a class="nav-link text-white fs-4 rounded" href="<?= $url('home/manutencoes') ?>">
                <i class="bi bi-tools"></i> Manutenções
            </a>
        </li>
        <li class="nav-item"><a class="nav-link text-white fs-4 rounded" href="<?= $url('home/categorias') ?>"><i class="bi bi-tags"></i> Categorias</a></li>
        <li class="nav-item"><a class="nav-link text-white fs-4 rounded" href="<?= $url('home/relatorios') ?>"><i class="bi bi-file-earmark-text"></i> Relatórios</a></li>
            <li class="nav-item">
                <a class="nav-link text-white fs-4 rounded" href="<?= $url('home/usuarios') ?>">
                    <i class="bi bi-people"></i> Usuários
                </a>
            </li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link text-white fs-4 rounded" href="<?= $url('home/conta') ?>"><i class="bi bi-person-lock"></i> Minha conta</a></li>
    </ul>

    <div class="mt-auto text-center px-3">
        <p class="text-white mb-2"><?= htmlspecialchars((string) ($_SESSION['usuario_nome'] ?? '')) ?></p>
        <form action="<?= $url('logout') ?>" method="post">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrfToken) ?>">
            <button class="btn btn-outline-light" type="submit">
                <i class="bi bi-box-arrow-right"></i> Sair
            </button>
        </form>
    </div>
</nav>
