<?php

declare(strict_types=1);

namespace Liga\Models;

final class Jugador extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT p.*, t.name AS team_name
             FROM players p
             JOIN teams t ON t.id = p.team_id
             ORDER BY t.name ASC, p.number ASC, p.name ASC'
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM players WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM players')->fetchColumn();
    }

    public function save(?int $id, int $teamId, string $name, string $position, int $number): void
    {
        $values = compact('teamId', 'name', 'position', 'number');
        if ($id === null) {
            $this->run(
                'INSERT INTO players (team_id, name, position, number)
                 VALUES (:teamId, :name, :position, :number)',
                $values
            );
            return;
        }

        $values['id'] = $id;
        $this->run(
            'UPDATE players SET team_id = :teamId, name = :name, position = :position, number = :number WHERE id = :id',
            $values
        );
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM players WHERE id = :id', ['id' => $id]);
    }
}
