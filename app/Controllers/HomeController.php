<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EquipamentosRepository;
use App\Services\AtivosService;
use Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $owner = $_SESSION['usuario_perfil'] === 'admin' ? null : (int) $_SESSION['usuario_id'];
        $rows = (new EquipamentosRepository())->select($owner);
        $summary = ['total' => count($rows), 'disponiveis' => 0, 'em_uso' => 0, 'manutencao' => 0];
        foreach ($rows as $row) {
            $key = $row['status'] === 'disponivel' ? 'disponiveis' : $row['status'];
            if (isset($summary[$key])) {
                $summary[$key]++;
            }
        }
        $loans = (new AtivosService())->loans($owner);
        $active = array_filter($loans, static fn (array $p): bool => $p['status'] === 'ativo');
        $this->view('home', [
            'equipamentosRecentes' => array_slice($rows, 0, 5), 'resumo' => $summary,
            'emprestimosAtivos' => count($active),
            'atrasados' => count(array_filter($active, static fn (array $p): bool => $p['data_devolucao_prevista'] !== null && $p['data_devolucao_prevista'] < date('Y-m-d'))),
        ]);
    }
}
