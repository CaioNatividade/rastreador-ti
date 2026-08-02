<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EquipamentosModel;
use Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $model = new EquipamentosModel();

        $this->view('home', [
            'dataEquipamentos' => $model->getAllRows(),
        ]);
    }

    public function equipamentos(): void
    {
        $this->view('equipamentos');
    }

    public function categorias(): void
    {
        $this->view('categorias');
    }

    public function emprestimos(): void
    {
        $model = new EquipamentosModel();

        $this->view('emprestimos', [
            'dataEquipamentos' => $model->getAllRows(),
        ]);
    }

    public function manutencoes(): void
    {
        $this->view('manutencoes');
    }

    public function usuarios(): void
    {
        $this->view('usuarios');
    }
}
