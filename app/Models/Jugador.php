<?php

declare(strict_types=1);

namespace Liga\Models;

final class Jugador extends Model
{
    public function all(?int $teamId = null): array
    {
        $filter = $teamId === null ? '' : 'WHERE p.equipo_id = :team_id';
        return $this->fetchAll(
            "SELECT p.id, p.equipo_id AS team_id, p.nombre AS name,
                    p.posicion AS position, p.numero AS number,
                    p.estatura AS height, p.fecha_nacimiento AS birth_date,
                    t.nombre AS team_name
             FROM jugadores p
             JOIN equipos t ON t.id = p.equipo_id
             {$filter}
             ORDER BY t.nombre ASC, p.numero ASC, p.nombre ASC",
            $teamId === null ? [] : ['team_id' => $teamId]
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, equipo_id AS team_id, nombre AS name, posicion AS position,
                    numero AS number, estatura AS height, fecha_nacimiento AS birth_date
             FROM jugadores WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM jugadores')->fetchColumn();
    }

    public function save(
        ?int $id,
        int $teamId,
        string $name,
        string $position,
        int $number,
        ?float $height = null,
        ?string $birthDate = null
    ): void
    {
        $values = compact('teamId', 'name', 'position', 'number', 'height', 'birthDate');
        if ($id === null) {
            $this->run(
                'INSERT INTO jugadores (equipo_id, nombre, posicion, numero, estatura, fecha_nacimiento)
                 VALUES (:teamId, :name, :position, :number, :height, :birthDate)',
                $values
            );
            return;
        }

        $values['id'] = $id;
        $this->run(
            'UPDATE jugadores
             SET equipo_id = :teamId, nombre = :name, posicion = :position, numero = :number,
                 estatura = :height, fecha_nacimiento = :birthDate
             WHERE id = :id',
            $values
        );
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM jugadores WHERE id = :id', ['id' => $id]);
    }
}
