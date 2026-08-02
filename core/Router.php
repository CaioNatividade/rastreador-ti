<?php

declare(strict_types=1);

namespace Core;

use App\Controllers\HomeController;

class Router
{
    /** @var array<string, array{class-string, string}> */
    private const ROUTES = [
        'home' => [HomeController::class, 'index'],
        'home/equipamentos' => [HomeController::class, 'equipamentos'],
        'home/categorias' => [HomeController::class, 'categorias'],
        'home/emprestimos' => [HomeController::class, 'emprestimos'],
        'home/manutencoes' => [HomeController::class, 'manutencoes'],
        'home/usuarios' => [HomeController::class, 'usuarios'],
    ];

    public function run(): void
    {
        $route = trim((string) ($_GET['rota'] ?? 'home'), '/');
        $route = $route === '' ? 'home' : $route;

        if (!isset(self::ROUTES[$route])) {
            $this->notFound();
            return;
        }

        [$controllerClass, $method] = self::ROUTES[$route];
        $controller = new $controllerClass();
        $controller->{$method}();
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '<h1>Erro 404</h1>';
        echo '<p>Página não encontrada.</p>';
    }
}
