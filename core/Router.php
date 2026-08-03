<?php

declare(strict_types=1);

namespace Core;

use App\Controllers\HomeController;
use App\Controllers\EquipamentosController;

class Router
{
    /** @var array<string, array<string, array{class-string, string}>> */
    private const ROUTES = [
        'GET' => [
            'home' => [HomeController::class, 'index'],
            'home/equipamentos' => [EquipamentosController::class, 'index'],
            'home/equipamentos/novo' => [EquipamentosController::class, 'create'],
            'home/categorias' => [HomeController::class, 'categorias'],
            'home/emprestimos' => [HomeController::class, 'emprestimos'],
            'home/manutencoes' => [HomeController::class, 'manutencoes'],
            'home/usuarios' => [HomeController::class, 'usuarios'],
        ],
        'POST' => [
            'home/equipamentos' => [EquipamentosController::class, 'store'],
        ],
    ];

    public function run(): void
    {
        $route = trim((string) ($_GET['rota'] ?? 'home'), '/');
        $route = $route === '' ? 'home' : $route;
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (!isset(self::ROUTES[$method][$route])) {
            if ($this->routeExistsForAnotherMethod($route)) {
                $this->methodNotAllowed();
                return;
            }

            $this->notFound();
            return;
        }

        [$controllerClass, $action] = self::ROUTES[$method][$route];
        $controller = new $controllerClass();
        $controller->{$action}();
    }

    private function routeExistsForAnotherMethod(string $route): bool
    {
        foreach (self::ROUTES as $routes) {
            if (isset($routes[$route])) {
                return true;
            }
        }

        return false;
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '<h1>Erro 404</h1>';
        echo '<p>Página não encontrada.</p>';
    }

    private function methodNotAllowed(): void
    {
        http_response_code(405);
        echo '<h1>Erro 405</h1>';
        echo '<p>Método não permitido para esta rota.</p>';
    }
}
