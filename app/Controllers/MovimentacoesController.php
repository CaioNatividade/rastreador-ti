<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AtivosService;
use Core\Controller;

class MovimentacoesController extends Controller
{
    public function emprestimos(): void
    {
        $service = new AtivosService();
        $admin = ($_SESSION['usuario_perfil'] ?? '') === 'admin';
        $this->view('emprestimos', [
            'emprestimos' => $service->loans($admin ? null : (int) $_SESSION['usuario_id']),
            'equipamentos' => $admin ? $service->query("SELECT id, nome, numero_serie FROM equipamentos WHERE status = 'disponivel' ORDER BY nome") : [],
            'colaboradores' => $admin ? $service->query("SELECT id, nome, email FROM usuarios WHERE ativo = 1 AND perfil = 'colaborador' ORDER BY nome") : [],
            'admin' => $admin,
            'semResponsavel' => $admin ? $service->query("SELECT e.id, e.nome, e.numero_serie FROM equipamentos e WHERE e.status = 'em_uso' AND NOT EXISTS (SELECT 1 FROM emprestimos p WHERE p.equipamento_id = e.id AND p.status = 'ativo')") : [],
        ]);
    }

    public function emprestar(): never
    {
        $this->submit(fn () => (new AtivosService())->lend($this->postedId('equipamento_id'), $this->postedId('colaborador_id'), (int) $_SESSION['usuario_id'], $this->field('previsao'), $this->field('observacoes')), 'home/emprestimos');
    }

    public function devolver(): never
    {
        $this->submit(fn () => (new AtivosService())->returnLoan($this->postedId(), $this->field('observacoes')), 'home/emprestimos');
    }

    public function manutencoes(): void
    {
        $service = new AtivosService();
        $this->view('manutencoes', [
            'manutencoes' => $service->query('SELECT m.*, e.nome AS equipamento, e.numero_serie FROM manutencoes m JOIN equipamentos e ON e.id = m.equipamento_id ORDER BY m.id DESC'),
            'equipamentos' => $service->query("SELECT e.id, e.nome, e.numero_serie, e.status FROM equipamentos e
                WHERE e.status IN ('disponivel', 'manutencao')
                AND NOT EXISTS (SELECT 1 FROM manutencoes m WHERE m.equipamento_id = e.id AND m.status = 'em_andamento')
                AND NOT EXISTS (SELECT 1 FROM emprestimos p WHERE p.equipamento_id = e.id AND p.status = 'ativo') ORDER BY e.nome"),
        ]);
    }

    public function abrirManutencao(): never
    {
        $this->submit(fn () => (new AtivosService())->startMaintenance($this->postedId('equipamento_id'), $this->field('descricao'), $this->field('inicio')), 'home/manutencoes');
    }

    public function concluirManutencao(): never
    {
        $this->submit(fn () => (new AtivosService())->finishMaintenance($this->postedId(), $this->field('fim'), $this->field('custo'), $this->field('destino'), $this->field('descricao')), 'home/manutencoes');
    }

    public function termo(): void
    {
        $id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
        $owner = $_SESSION['usuario_perfil'] === 'admin' ? null : (int) $_SESSION['usuario_id'];
        $loan = null;
        foreach ((new AtivosService())->loans($owner) as $row) {
            if ((int) $row['id'] === $id) {
                $loan = $row;
                break;
            }
        }
        if ($loan === null) {
            http_response_code(404);
            echo 'Empréstimo não encontrado.';
            return;
        }
        $this->view('termo', ['emprestimo' => $loan]);
    }
}
