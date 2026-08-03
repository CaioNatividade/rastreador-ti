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
        $equipamentos = $model->getAllRows();

        $this->view('home', [
            'equipamentosRecentes' => array_slice($equipamentos, 0, 5),
            'resumo' => $model->getDashboardSummary(),
        ]);
    }

    public function categorias(): void
    {
        $this->view('categorias');
    }

    public function emprestimos(): void
    {
        $this->view('emprestimos');
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
