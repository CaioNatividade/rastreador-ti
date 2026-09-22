<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UsuarioModel;
use Core\Controller;
use Core\Csrf;

class LoginController extends Controller
{
    public function index(): void
    {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('home');
        }

        $this->viewAuth('login');
    }

    public function autenticar(): void
    {
        Csrf::validar();

        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');
        $usuario = (new UsuarioModel())->autenticar($email, $senha);

        if ($usuario !== null) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int) $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_perfil'] = $usuario['perfil'];
            $_SESSION['senha_versao'] = hash('sha256', $usuario['senha_hash']);
            unset($_SESSION['csrf_token']);
            $this->redirect('home');
        }

        $this->viewAuth('login', [
            'erro' => 'E-mail ou senha inválidos.',
            'email' => $email,
        ]);
    }

    public function logout(): void
    {
        Csrf::validar();

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
        $this->redirect('login');
    }
}
