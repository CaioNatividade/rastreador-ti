<?php

declare(strict_types=1);

namespace App\Services;

use Config\Database;
use DomainException;
use PDO;
use Throwable;

class CadastrosService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function categories(): array
    {
        return $this->db->query('SELECT c.*, (SELECT COUNT(*) FROM equipamentos e WHERE e.categoria_id = c.id) AS total FROM categorias c ORDER BY c.nome')->fetchAll();
    }

    public function saveCategory(?int $id, string $name, string $description): void
    {
        AtivosService::text($name, 100, 'Nome', true);
        AtivosService::text($description, 255, 'Descrição');
        $stmt = $this->db->prepare('SELECT id FROM categorias WHERE nome = ? AND id <> ?');
        $stmt->execute([$name, $id ?? 0]);
        if ($stmt->fetch()) {
            throw new DomainException('Já existe uma categoria com esse nome.');
        }
        if ($id !== null) {
            $stmt = $this->db->prepare('SELECT id FROM categorias WHERE id = ?');
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                throw new DomainException('Categoria não encontrada.');
            }
        }
        $stmt = $this->db->prepare($id === null
            ? 'INSERT INTO categorias (nome, descricao) VALUES (?, ?)'
            : 'UPDATE categorias SET nome = ?, descricao = ? WHERE id = ?');
        $stmt->execute($id === null ? [$name, $description] : [$name, $description, $id]);
    }

    public function deleteCategory(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM categorias WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->rowCount()) {
            throw new DomainException('Categoria não encontrada.');
        }
    }

    public function users(): array
    {
        return $this->db->query('SELECT id, nome, email, perfil, ativo FROM usuarios ORDER BY nome')->fetchAll();
    }

    public function saveUser(?int $id, array $input, int $actor): void
    {
        AtivosService::text($input['nome'], 150, 'Nome', true);
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL) || strlen($input['email']) > 150) {
            throw new DomainException('Informe um e-mail válido com até 150 caracteres.');
        }
        if (!in_array($input['perfil'], ['admin', 'colaborador'], true) || !in_array($input['ativo'], ['0', '1'], true)) {
            throw new DomainException('Perfil ou situação inválida.');
        }
        if ($id === null || $input['senha'] !== '') {
            self::validatePassword($input['senha']);
        }
        $this->db->beginTransaction();
        try {
            // Serializa mudanças de perfil para não remover dois últimos admins simultaneamente.
            $users = $this->db->query('SELECT id, email, perfil, ativo FROM usuarios ORDER BY id FOR UPDATE')->fetchAll();
            $found = $id === null;
            $otherAdmins = 0;
            foreach ($users as $user) {
                if ((int) $user['id'] === $id) {
                    $found = true;
                } else {
                    if (strcasecmp($user['email'], $input['email']) === 0) {
                        throw new DomainException('Este e-mail já está cadastrado.');
                    }
                    $otherAdmins += $user['perfil'] === 'admin' && $user['ativo'] ? 1 : 0;
                }
            }
            if (!$found) {
                throw new DomainException('Usuário não encontrado.');
            }
            if ($id === $actor && ($input['ativo'] !== '1' || $input['perfil'] !== 'admin')) {
                throw new DomainException('Você não pode desativar ou remover seu próprio perfil de administrador.');
            }
            if ($id !== null && $otherAdmins === 0 && ($input['ativo'] !== '1' || $input['perfil'] !== 'admin')) {
                throw new DomainException('Mantenha pelo menos um administrador ativo.');
            }
            if ($id === null) {
                $stmt = $this->db->prepare('INSERT INTO usuarios (nome, email, perfil, ativo, senha_hash) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$input['nome'], $input['email'], $input['perfil'], $input['ativo'], password_hash($input['senha'], PASSWORD_DEFAULT)]);
            } else {
                $sql = 'UPDATE usuarios SET nome = ?, email = ?, perfil = ?, ativo = ?';
                $params = [$input['nome'], $input['email'], $input['perfil'], $input['ativo']];
                if ($input['senha'] !== '') {
                    $sql .= ', senha_hash = ?';
                    $params[] = password_hash($input['senha'], PASSWORD_DEFAULT);
                }
                $params[] = $id;
                $stmt = $this->db->prepare($sql . ' WHERE id = ?');
                $stmt->execute($params);
            }
            $this->db->commit();
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function changePassword(int $id, string $current, string $password, string $confirmation): void
    {
        self::validatePassword($password);
        if ($password !== $confirmation) {
            throw new DomainException('A confirmação da nova senha não confere.');
        }
        $stmt = $this->db->prepare('SELECT senha_hash FROM usuarios WHERE id = ? AND ativo = 1');
        $stmt->execute([$id]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($current, $hash)) {
            throw new DomainException('Senha atual incorreta.');
        }
        $stmt = $this->db->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ? AND senha_hash = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id, $hash]);
        if (!$stmt->rowCount()) {
            throw new DomainException('A senha foi alterada em outra sessão. Entre novamente.');
        }
    }

    private static function validatePassword(string $password): void
    {
        if (mb_strlen($password) < 10 || strlen($password) > 72) {
            throw new DomainException('Use uma senha com pelo menos 10 caracteres e no máximo 72 bytes.');
        }
    }
}
