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

    public function insert(EquipamentosModel $model): void
    {
        // Implementação prevista para a etapa de cadastro da Entrega 3.
    }

    public function update(EquipamentosModel $model): void
    {
        // Implementação prevista para a etapa de atualização do CRUD.
    }

    /** @return array<int, array<string, mixed>> */
    public function select(): array
    {
        $statement = $this->connection->prepare('SELECT * FROM equipamentos');
        $statement->execute();

        return $statement->fetchAll();
    }
}
