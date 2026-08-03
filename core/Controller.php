<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

abstract class Controller
{
    /** @param array<string, mixed> $data */
    protected function view(string $viewName, array $data = []): void
    {
        $viewsDirectory = dirname(__DIR__) . '/app/Views';
        $view = $viewsDirectory . '/' . $viewName . '.php';

        if (!is_file($view)) {
            throw new RuntimeException('View não encontrada.');
        }

        extract($data, EXTR_SKIP);
        require $viewsDirectory . '/layouts/main.php';
    }

    protected function redirect(string $route): never
    {
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        header('Location: ' . $basePath . '/' . ltrim($route, '/'));
        exit;
    }
}
