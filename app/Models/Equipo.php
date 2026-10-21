<?php

declare(strict_types=1);

namespace Liga\Models;

final class Equipo extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM teams ORDER BY name ASC');
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM teams')->fetchColumn();
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM teams WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function save(?int $id, string $name, string $city): void
    {
        if ($id === null) {
            $this->run('INSERT INTO teams (name, city) VALUES (:name, :city)', compact('name', 'city'));
            return;
        }

        $this->run(
            'UPDATE teams SET name = :name, city = :city WHERE id = :id',
            compact('name', 'city', 'id')
        );
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM teams WHERE id = :id', ['id' => $id]);
    }
}
