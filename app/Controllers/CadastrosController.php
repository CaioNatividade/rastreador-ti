<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CadastrosService;
use Core\Controller;

class CadastrosController extends Controller
{
    public function categorias(): void
    {
        $this->view('categorias', ['categorias' => (new CadastrosService())->categories()]);
    }

    public function salvarCategoria(): never
    {
        $this->submit(fn () => (new CadastrosService())->saveCategory($this->field('id') === '' ? null : $this->postedId(), $this->field('nome'), $this->field('descricao')), 'home/categorias');
    }

    public function excluirCategoria(): never
    {
        $this->submit(fn () => (new CadastrosService())->deleteCategory($this->postedId()), 'home/categorias');
    }

    public function usuarios(): void
    {
        $this->view('usuarios', ['usuarios' => (new CadastrosService())->users()]);
    }

    public function salvarUsuario(): never
    {
        $this->submit(function (): void {
            $id = $this->field('id') === '' ? null : $this->postedId();
            (new CadastrosService())->saveUser($id, [
                'nome' => $this->field('nome'), 'email' => $this->field('email'),
                'perfil' => $this->field('perfil'), 'ativo' => $this->field('ativo'), 'senha' => $this->field('senha', false),
            ], (int) $_SESSION['usuario_id']);
            // Mudança de senha pelo cadastro encerra as sessões anteriores, inclusive a própria.
        }, 'home/usuarios');
    }

    public function conta(): void
    {
        $this->view('conta');
    }

    public function senha(): never
    {
        $this->submit(function (): void {
            (new CadastrosService())->changePassword((int) $_SESSION['usuario_id'], $this->field('senha_atual', false), $this->field('senha', false), $this->field('confirmacao', false));
            $user = (new \App\Models\UsuarioRepository())->findActive((int) $_SESSION['usuario_id']);
            session_regenerate_id(true);
            $_SESSION['senha_versao'] = hash('sha256', $user['senha_hash']);
        }, 'home/conta');
    }
}
