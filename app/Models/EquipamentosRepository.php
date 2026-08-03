<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;

class EquipamentosRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance();
    }

    public function insert(EquipamentosModel $model): int
    {
        $sql = <<<'SQL'
            INSERT INTO equipamentos (
                nome,
                marca,
                modelo,
                numero_serie,
                categoria_id,
                status,
                data_aquisicao,
                observacoes
            ) VALUES (
                :nome,
                :marca,
                :modelo,
                :numero_serie,
                :categoria_id,
                :status,
                :data_aquisicao,
                :observacoes
            )
            SQL;

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'nome' => $model->nome,
            'marca' => $model->marca,
            'modelo' => $model->modelo,
            'numero_serie' => $model->numeroSerie,
            'categoria_id' => $model->categoriaId,
            'status' => $model->status,
            'data_aquisicao' => $model->dataAquisicao,
            'observacoes' => $model->observacoes,
        ]);

        return (int) $this->connection->lastInsertId();
    }

    public function update(EquipamentosModel $model): void
    {
        // Implementação prevista para a etapa de atualização do CRUD.
    }

    /** @return array<int, array<string, mixed>> */
    public function select(): array
    {
        $sql = <<<'SQL'
            SELECT
                equipamentos.id,
                equipamentos.nome,
                equipamentos.marca,
                equipamentos.modelo,
                equipamentos.numero_serie,
                equipamentos.status,
                equipamentos.data_aquisicao,
                equipamentos.observacoes,
                equipamentos.criado_em,
                equipamentos.atualizado_em,
                categorias.id AS categoria_id,
                categorias.nome AS categoria_nome
            FROM equipamentos
            INNER JOIN categorias ON categorias.id = equipamentos.categoria_id
            ORDER BY equipamentos.id DESC
            SQL;

        $statement = $this->connection->prepare($sql);
        $statement->execute();

        return $statement->fetchAll();
    }

    /** @return array<int, array{id: int, nome: string}> */
    public function selectCategories(): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, nome FROM categorias ORDER BY nome'
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function serialNumberExists(string $serialNumber): bool
    {
        $statement = $this->connection->prepare(
            'SELECT 1 FROM equipamentos WHERE numero_serie = :numero_serie LIMIT 1'
        );
        $statement->execute(['numero_serie' => $serialNumber]);

        return $statement->fetchColumn() !== false;
    }

    public function categoryExists(int $categoryId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT 1 FROM categorias WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => $categoryId]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * @return array{total: int, disponiveis: int, em_uso: int, manutencao: int}
     */
    public function selectDashboardSummary(): array
    {
        $sql = <<<'SQL'
            SELECT
                COUNT(*) AS total,
                COALESCE(SUM(status = 'disponivel'), 0) AS disponiveis,
                COALESCE(SUM(status = 'em_uso'), 0) AS em_uso,
                COALESCE(SUM(status = 'manutencao'), 0) AS manutencao
            FROM equipamentos
            SQL;

        $summary = $this->connection->query($sql)->fetch();

        return [
            'total' => (int) $summary['total'],
            'disponiveis' => (int) $summary['disponiveis'],
            'em_uso' => (int) $summary['em_uso'],
            'manutencao' => (int) $summary['manutencao'],
        ];
    }
}
