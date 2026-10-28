<?php

declare(strict_types=1);

namespace Liga\Models;

final class Equipo extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT id, nombre AS name, ciudad AS city, url_logo AS logo_url,
                    entrenador AS coach, anio_fundacion AS founded_year
             FROM equipos ORDER BY nombre ASC'
        );
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM equipos')->fetchColumn();
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, nombre AS name, ciudad AS city, url_logo AS logo_url,
                    entrenador AS coach, anio_fundacion AS founded_year
             FROM equipos WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
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
                'INSERT INTO equipos (nombre, ciudad, url_logo, entrenador, anio_fundacion)
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
            'UPDATE equipos
             SET nombre = :name, ciudad = :city, url_logo = :logo_url,
                 entrenador = :coach, anio_fundacion = :founded_year
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
        $this->run('DELETE FROM equipos WHERE id = :id', ['id' => $id]);
    }
}
