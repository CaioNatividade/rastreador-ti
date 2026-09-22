<?php

declare(strict_types=1);

namespace App\Models;

use Config\Database;
use PDO;

class UsuarioRepository
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance();
    }

    /** @return array{id: int, nome: string, email: string, perfil: string}|null */
    public function autenticar(string $email, string $senha): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, nome, email, senha_hash, perfil
             FROM usuarios
             WHERE email = :email AND ativo = 1
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $usuario = $statement->fetch();

        if ($usuario === false || !password_verify($senha, $usuario['senha_hash'])) {
            return null;
        }

        return $usuario;
    }

    public function findActive(int $id): ?array
    {
        $statement = $this->connection->prepare('SELECT id, nome, email, perfil, senha_hash FROM usuarios WHERE id = ? AND ativo = 1');
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }
}
