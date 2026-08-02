<?php
/** @var string $basePath */
$url = static fn(string $route): string => htmlspecialchars($basePath . '/' . $route);
?>
<nav class="d-flex flex-column align-items-center py-4 bg-primary text-dark" style="width: 300px; min-height: 100vh;">
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
        <li class="nav-item">
            <a class="nav-link text-white fs-4 rounded" href="<?= $url('home/manutencoes') ?>">
                <i class="bi bi-tools"></i> Manutenções
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white fs-4 rounded" href="<?= $url('home/usuarios') ?>">
                <i class="bi bi-people"></i> Usuários
            </a>
        </li>
    </ul>
</nav>