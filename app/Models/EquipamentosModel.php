<?php

declare(strict_types=1);

namespace App\Models;

class EquipamentosModel
{
    public ?int $id = null;
    public int $categoriaId;
    public string $nome;
    public ?string $marca = null;
    public ?string $modelo = null;
    public string $numeroSerie;
    public string $status = 'disponivel';
    public ?string $dataAquisicao = null;
    public ?string $observacoes = null;

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    public function save(): int
    {
        $repository = new EquipamentosRepository();

        if ($this->id !== null) {
            $repository->update($this);
            return $this->id;
        }

        $this->id = $repository->insert($this);

        return $this->id;
    }

    /** @return array<int, array<string, mixed>> */
    public function getAllRows(): array
    {
        $repository = new EquipamentosRepository();
        $this->rows = $repository->select();

        return $this->rows;
    }

    /**
     * @return array{total: int, disponiveis: int, em_uso: int, manutencao: int}
     */
    public function getDashboardSummary(): array
    {
        return (new EquipamentosRepository())->selectDashboardSummary();
    }
}
