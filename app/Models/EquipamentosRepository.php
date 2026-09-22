<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;

class EquipamentosRepository
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::getInstance();
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
        $statement = $this->connection->prepare(
            'UPDATE equipamentos SET nome = :nome, marca = :marca, modelo = :modelo,
             numero_serie = :numero_serie, categoria_id = :categoria_id, status = :status,
             data_aquisicao = :data_aquisicao, observacoes = :observacoes WHERE id = :id'
        );
        $statement->execute([
            'id' => $model->id,
            'nome' => $model->nome,
            'marca' => $model->marca,
            'modelo' => $model->modelo,
            'numero_serie' => $model->numeroSerie,
            'categoria_id' => $model->categoriaId,
            'status' => $model->status,
            'data_aquisicao' => $model->dataAquisicao,
            'observacoes' => $model->observacoes,
        ]);
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM equipamentos WHERE id = :id');
        $statement->execute(['id' => $id]);
        $equipment = $statement->fetch();

        return $equipment === false ? null : $equipment;
    }

    public function delete(int $id): bool
    {
        $statement = $this->connection->prepare('DELETE FROM equipamentos WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    /** @return array<int, array<string, mixed>> */
    public function select(?int $owner = null): array
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
            SQL;

        if ($owner !== null) {
            $sql .= " WHERE EXISTS (SELECT 1 FROM emprestimos p WHERE p.equipamento_id = equipamentos.id AND p.colaborador_id = ? AND p.status = 'ativo')";
        }
        $sql .= ' ORDER BY equipamentos.id DESC';

        $statement = $this->connection->prepare($sql);
        $statement->execute($owner === null ? [] : [$owner]);

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

    public function serialNumberExists(string $serialNumber, ?int $ignoredId = null): bool
    {
        $statement = $this->connection->prepare(
            'SELECT 1 FROM equipamentos
             WHERE numero_serie = :numero_serie
               AND (:ignored_id_null IS NULL OR id <> :ignored_id_value) LIMIT 1'
        );
        $statement->execute([
            'numero_serie' => $serialNumber,
            'ignored_id_null' => $ignoredId,
            'ignored_id_value' => $ignoredId ?? 0,
        ]);

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
