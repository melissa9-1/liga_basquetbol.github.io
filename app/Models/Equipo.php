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

    public function save(
        ?int $id,
        string $name,
        string $city,
        ?string $logoUrl = null,
        ?string $coach = null,
        ?int $foundedYear = null
    ): void
    {
        if ($id === null) {
            $this->run(
                'INSERT INTO teams (name, city, logo_url, coach, founded_year)
                 VALUES (:name, :city, :logo_url, :coach, :founded_year)',
                [
                    'name' => $name,
                    'city' => $city,
                    'logo_url' => $logoUrl,
                    'coach' => $coach,
                    'founded_year' => $foundedYear,
                ]
            );
            return;
        }

        $this->run(
            'UPDATE teams
             SET name = :name, city = :city, logo_url = :logo_url, coach = :coach, founded_year = :founded_year
             WHERE id = :id',
            [
                'name' => $name,
                'city' => $city,
                'logo_url' => $logoUrl,
                'coach' => $coach,
                'founded_year' => $foundedYear,
                'id' => $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM teams WHERE id = :id', ['id' => $id]);
    }
}
