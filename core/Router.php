<?php

declare(strict_types=1);

namespace Core;

use App\Controllers\HomeController;
use App\Controllers\EquipamentosController;
use App\Controllers\LoginController;
use App\Controllers\CadastrosController;
use App\Controllers\MovimentacoesController;
use App\Models\UsuarioRepository;

class Router
{
    private const PUBLIC_ROUTES = ['login', 'login/autenticar'];

    /** @var array<string, array<int, string>> */
    private const ADMIN_ONLY_ROUTES = [
        'GET' => [
            'home/equipamentos/novo',
            'home/equipamentos/editar',
            'home/usuarios',
            'home/categorias',
            'home/manutencoes',
            'home/relatorios',
            'home/relatorios/csv',
        ],
        'POST' => [
            'home/equipamentos',
            'home/equipamentos/atualizar',
            'home/equipamentos/excluir',
        ],
    ];

    /** @var array<string, array<string, array{class-string, string}>> */
    private const ROUTES = [
        'GET' => [
            'login' => [LoginController::class, 'index'],
            'home' => [HomeController::class, 'index'],
            'home/equipamentos' => [EquipamentosController::class, 'index'],
            'home/equipamentos/novo' => [EquipamentosController::class, 'create'],
            'home/equipamentos/editar' => [EquipamentosController::class, 'edit'],
            'home/categorias' => [CadastrosController::class, 'categorias'],
            'home/emprestimos' => [MovimentacoesController::class, 'emprestimos'],
            'home/emprestimos/termo' => [MovimentacoesController::class, 'termo'],
            'home/manutencoes' => [MovimentacoesController::class, 'manutencoes'],
            'home/usuarios' => [CadastrosController::class, 'usuarios'],
            'home/conta' => [CadastrosController::class, 'conta'],
            'home/relatorios' => [EquipamentosController::class, 'report'],
            'home/relatorios/csv' => [EquipamentosController::class, 'csv'],
        ],
        'POST' => [
            'home/equipamentos' => [EquipamentosController::class, 'store'],
            'home/equipamentos/atualizar' => [EquipamentosController::class, 'update'],
            'home/equipamentos/excluir' => [EquipamentosController::class, 'destroy'],
            'login/autenticar' => [LoginController::class, 'autenticar'],
            'logout' => [LoginController::class, 'logout'],
            'home/categorias/salvar' => [CadastrosController::class, 'salvarCategoria'],
            'home/categorias/excluir' => [CadastrosController::class, 'excluirCategoria'],
            'home/usuarios/salvar' => [CadastrosController::class, 'salvarUsuario'],
            'home/conta/senha' => [CadastrosController::class, 'senha'],
            'home/emprestimos' => [MovimentacoesController::class, 'emprestar'],
            'home/emprestimos/devolver' => [MovimentacoesController::class, 'devolver'],
            'home/manutencoes' => [MovimentacoesController::class, 'abrirManutencao'],
            'home/manutencoes/concluir' => [MovimentacoesController::class, 'concluirManutencao'],
        ],
    ];

    public function run(): void
    {
        $route = trim((string) ($_GET['rota'] ?? 'login'), '/');
        $route = $route === '' ? 'login' : $route;
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (isset($_SESSION['usuario_id'])) {
            $user = (new UsuarioRepository())->findActive((int) $_SESSION['usuario_id']);
            if ($user === null || (isset($_SESSION['senha_versao']) && !hash_equals($_SESSION['senha_versao'], hash('sha256', $user['senha_hash'])))) {
                $_SESSION = [];
                session_regenerate_id(true);
                $this->redirectToLogin();
                return;
            }
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_perfil'] = $user['perfil'];
            $_SESSION['senha_versao'] = hash('sha256', $user['senha_hash']);
        }

        if (!in_array($route, self::PUBLIC_ROUTES, true) && !isset($_SESSION['usuario_id'])) {
            $this->redirectToLogin();
            return;
        }

        if (!isset(self::ROUTES[$method][$route])) {
            if ($this->routeExistsForAnotherMethod($route)) {
                $this->methodNotAllowed();
                return;
            }

            $this->notFound();
            return;
        }

        if ($this->isAdminOnly($method, $route) && !$this->isAdmin()) {
            $this->forbidden();
            return;
        }

        [$controllerClass, $action] = self::ROUTES[$method][$route];
        $controller = new $controllerClass();
        $controller->{$action}();
    }

    private function isAdminOnly(string $method, string $route): bool
    {
        if ($method === 'POST' && !in_array($route, ['login/autenticar', 'logout', 'home/conta/senha'], true)) {
            return true;
        }
        return in_array($route, self::ADMIN_ONLY_ROUTES[$method] ?? [], true);
    }

    private function isAdmin(): bool
    {
        return ($_SESSION['usuario_perfil'] ?? '') === 'admin';
    }

    private function redirectToLogin(): void
    {
        $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        header('Location: ' . $basePath . '/login');
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

    private function forbidden(): void
    {
        http_response_code(403);
        echo '<h1>Erro 403</h1>';
        echo '<p>Você não tem permissão para acessar esta página.</p>';
    }
}
