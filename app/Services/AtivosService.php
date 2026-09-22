<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EquipamentosModel;
use App\Models\EquipamentosRepository;
use Config\Database;
use DomainException;
use PDO;
use Throwable;

/** Operações de movimentação: bloqueio do equipamento antes de alterar seu histórico. */
class AtivosService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function execute(string $sql, array $params = []): void
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    private function transaction(callable $action): mixed
    {
        $this->db->beginTransaction();
        try {
            $result = $action();
            $this->db->commit();
            return $result;
        } catch (Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    private function equipment(int $id): array
    {
        $row = $this->query('SELECT * FROM equipamentos WHERE id = ? FOR UPDATE', [$id])[0] ?? null;
        if (!$row) {
            throw new DomainException('Equipamento não encontrado.');
        }
        return $row;
    }

    private function activeLoan(int $id): bool
    {
        return $this->query("SELECT id FROM emprestimos WHERE equipamento_id = ? AND status = 'ativo'", [$id]) !== [];
    }

    private function activeMaintenance(int $id): bool
    {
        return $this->query("SELECT id FROM manutencoes WHERE equipamento_id = ? AND status = 'em_andamento'", [$id]) !== [];
    }

    public function saveEquipment(EquipamentosModel $model): int
    {
        return $this->transaction(function () use ($model): int {
            $old = $model->id === null ? null : $this->equipment($model->id);
            if ($old && $this->activeLoan($model->id) && $model->status !== 'em_uso') {
                throw new DomainException('Registre a devolução em Empréstimos antes de alterar o status.');
            }
            if ($old && $this->activeMaintenance($model->id) && $model->status !== 'manutencao') {
                throw new DomainException('Conclua a manutenção na aba Manutenções antes de alterar o status.');
            }
            if ($model->status === 'em_uso' && (!$old || $old['status'] !== 'em_uso')) {
                throw new DomainException('Para colocar um equipamento em uso, registre um empréstimo e seu responsável.');
            }
            $repo = new EquipamentosRepository($this->db);
            if ($model->id === null) {
                $model->id = $repo->insert($model);
            } else {
                $repo->update($model);
            }
            if ($model->status === 'manutencao' && !$this->activeMaintenance($model->id)) {
                $this->execute("INSERT INTO manutencoes (equipamento_id, descricao, data_inicio) VALUES (?, ?, ?)", [
                    $model->id, 'Manutenção aberta pelo cadastro de equipamentos. ' . ($model->observacoes ?? ''), date('Y-m-d'),
                ]);
            }
            return $model->id;
        });
    }

    public function deleteEquipment(int $id): void
    {
        $this->transaction(function () use ($id): void {
            $this->equipment($id);
            if ($this->query('SELECT id FROM emprestimos WHERE equipamento_id = ? LIMIT 1', [$id])
                || $this->query('SELECT id FROM manutencoes WHERE equipamento_id = ? LIMIT 1', [$id])) {
                throw new DomainException('O equipamento possui histórico. Use o status Baixado para preservá-lo.');
            }
            $this->execute('DELETE FROM equipamentos WHERE id = ?', [$id]);
        });
    }

    public function lend(int $equipmentId, int $userId, int $actorId, string $due, string $notes): int
    {
        return $this->transaction(function () use ($equipmentId, $userId, $actorId, $due, $notes): int {
            $equipment = $this->equipment($equipmentId);
            if ($equipment['status'] !== 'disponivel' || $this->activeLoan($equipmentId) || $this->activeMaintenance($equipmentId)) {
                throw new DomainException('O equipamento não está disponível para empréstimo.');
            }
            $user = $this->query('SELECT id, ativo, perfil FROM usuarios WHERE id = ? FOR UPDATE', [$userId])[0] ?? null;
            if (!$user || !$user['ativo'] || $user['perfil'] !== 'colaborador') {
                throw new DomainException('Selecione um colaborador ativo.');
            }
            if (!self::validDate($due) || $due < date('Y-m-d')) {
                throw new DomainException('A devolução prevista deve ser hoje ou uma data futura válida.');
            }
            self::text($notes, 5000, 'Observações');
            $this->execute('INSERT INTO emprestimos (equipamento_id, colaborador_id, responsavel_entrega_id, data_entrega, data_devolucao_prevista, observacoes_entrega) VALUES (?, ?, ?, ?, ?, ?)', [
                $equipmentId, $userId, $actorId, date('Y-m-d H:i:s'), $due, $notes,
            ]);
            $id = (int) $this->db->lastInsertId();
            $this->execute("UPDATE equipamentos SET status = 'em_uso' WHERE id = ?", [$equipmentId]);
            return $id;
        });
    }

    public function returnLoan(int $id, string $notes): void
    {
        $this->transaction(function () use ($id, $notes): void {
            $loan = $this->query('SELECT equipamento_id FROM emprestimos WHERE id = ?', [$id])[0] ?? null;
            if (!$loan) {
                throw new DomainException('Empréstimo não encontrado.');
            }
            $this->equipment((int) $loan['equipamento_id']);
            $loan = $this->query('SELECT * FROM emprestimos WHERE id = ? FOR UPDATE', [$id])[0];
            if ($loan['status'] !== 'ativo') {
                throw new DomainException('Este empréstimo já foi devolvido.');
            }
            if ($this->activeMaintenance((int) $loan['equipamento_id'])) {
                throw new DomainException('Há uma manutenção aberta para este equipamento. Conclua-a antes da devolução.');
            }
            self::text($notes, 5000, 'Observações');
            $this->execute("UPDATE emprestimos SET status = 'devolvido', data_devolucao = ?, observacoes_devolucao = ? WHERE id = ?", [date('Y-m-d H:i:s'), $notes, $id]);
            $this->execute("UPDATE equipamentos SET status = 'disponivel' WHERE id = ?", [$loan['equipamento_id']]);
        });
    }

    public function startMaintenance(int $equipmentId, string $description, string $start): void
    {
        $this->transaction(function () use ($equipmentId, $description, $start): void {
            $equipment = $this->equipment($equipmentId);
            if (!in_array($equipment['status'], ['disponivel', 'manutencao'], true) || $this->activeLoan($equipmentId)) {
                throw new DomainException('Devolva o equipamento antes da manutenção. Equipamentos baixados não podem entrar em manutenção.');
            }
            if ($this->activeMaintenance($equipmentId)) {
                throw new DomainException('Já existe uma manutenção aberta para este equipamento.');
            }
            self::text($description, 5000, 'Descrição', true);
            if (!self::validDate($start) || $start > date('Y-m-d')) {
                throw new DomainException('Informe uma data de início válida, até hoje.');
            }
            $this->execute('INSERT INTO manutencoes (equipamento_id, descricao, data_inicio) VALUES (?, ?, ?)', [$equipmentId, $description, $start]);
            $this->execute("UPDATE equipamentos SET status = 'manutencao' WHERE id = ?", [$equipmentId]);
        });
    }

    public function finishMaintenance(int $id, string $end, string $cost, string $destination, string $description): void
    {
        $this->transaction(function () use ($id, $end, $cost, $destination, $description): void {
            $row = $this->query('SELECT equipamento_id FROM manutencoes WHERE id = ?', [$id])[0] ?? null;
            if (!$row) {
                throw new DomainException('Manutenção não encontrada.');
            }
            $this->equipment((int) $row['equipamento_id']);
            $row = $this->query('SELECT * FROM manutencoes WHERE id = ? FOR UPDATE', [$id])[0];
            if ($row['status'] !== 'em_andamento') {
                throw new DomainException('Esta manutenção já foi concluída.');
            }
            if ($this->activeLoan((int) $row['equipamento_id'])) {
                throw new DomainException('Este equipamento possui empréstimo ativo. Regularize a devolução primeiro.');
            }
            if (!self::validDate($end) || $end < $row['data_inicio'] || $end > date('Y-m-d')) {
                throw new DomainException('A conclusão deve estar entre o início da manutenção e hoje.');
            }
            $cost = str_replace(',', '.', $cost);
            if ($cost !== '' && !preg_match('/^\d{1,8}(\.\d{1,2})?$/D', $cost)) {
                throw new DomainException('Informe um custo válido, positivo ou zero, com até duas casas decimais.');
            }
            if (!in_array($destination, ['disponivel', 'baixado'], true)) {
                throw new DomainException('Selecione Disponível ou Baixado.');
            }
            self::text($description, 5000, 'Descrição do serviço', true);
            $this->execute("UPDATE manutencoes SET status = 'concluida', data_fim = ?, custo = ?, descricao = ? WHERE id = ?", [$end, $cost === '' ? null : $cost, $description, $id]);
            $this->execute('UPDATE equipamentos SET status = ? WHERE id = ?', [$destination, $row['equipamento_id']]);
        });
    }

    public function loans(?int $owner = null): array
    {
        return $this->query('SELECT p.*, e.nome AS equipamento, e.numero_serie, u.nome AS colaborador, u.email,
            a.nome AS responsavel FROM emprestimos p JOIN equipamentos e ON e.id = p.equipamento_id
            JOIN usuarios u ON u.id = p.colaborador_id LEFT JOIN usuarios a ON a.id = p.responsavel_entrega_id'
            . ($owner === null ? '' : ' WHERE p.colaborador_id = ?') . ' ORDER BY p.id DESC', $owner === null ? [] : [$owner]);
    }

    public static function validDate(string $date): bool
    {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    public static function text(string $text, int $max, string $label, bool $required = false): void
    {
        if (($required && trim($text) === '') || mb_strlen($text) > $max) {
            throw new DomainException("$label: preencha corretamente (máximo de $max caracteres).");
        }
    }
}
