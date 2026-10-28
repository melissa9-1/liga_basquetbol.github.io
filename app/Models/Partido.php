<?php

declare(strict_types=1);

namespace Liga\Models;

final class Partido extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT m.id, m.equipo_local_id AS local_team_id,
                    m.equipo_visitante_id AS visitor_team_id,
                    m.puntos_local AS local_score, m.puntos_visitante AS visitor_score,
                    m.fecha_juego AS played_on, m.hora_juego AS played_at,
                    m.sede AS venue, m.jugado AS played,
                    home.nombre AS local_name, home.ciudad AS local_city,
                    away.nombre AS visitor_name, away.ciudad AS visitor_city
             FROM partidos m
             JOIN equipos home ON home.id = m.equipo_local_id
             JOIN equipos away ON away.id = m.equipo_visitante_id
             ORDER BY m.fecha_juego DESC, m.id DESC'
        );
    }

    public function recent(int $limit = 4): array
    {
        $limit = max(1, min(100, $limit));
        return $this->fetchAll(
            'SELECT m.id, m.equipo_local_id AS local_team_id,
                    m.equipo_visitante_id AS visitor_team_id,
                    m.puntos_local AS local_score, m.puntos_visitante AS visitor_score,
                    m.fecha_juego AS played_on, m.hora_juego AS played_at,
                    m.sede AS venue, m.jugado AS played,
                    home.nombre AS local_name, home.ciudad AS local_city,
                    away.nombre AS visitor_name, away.ciudad AS visitor_city
             FROM partidos m
             JOIN equipos home ON home.id = m.equipo_local_id
             JOIN equipos away ON away.id = m.equipo_visitante_id
             WHERE m.jugado = 1
             ORDER BY m.fecha_juego DESC, m.id DESC
             LIMIT ' . $limit
        );
    }

    public function completed(): array
    {
        return $this->fetchAll(
            'SELECT m.id, m.equipo_local_id AS local_team_id,
                    m.equipo_visitante_id AS visitor_team_id,
                    m.puntos_local AS local_score, m.puntos_visitante AS visitor_score,
                    m.fecha_juego AS played_on, m.hora_juego AS played_at,
                    m.sede AS venue, m.jugado AS played,
                    home.nombre AS local_name, home.ciudad AS local_city,
                    away.nombre AS visitor_name, away.ciudad AS visitor_city
             FROM partidos m
             JOIN equipos home ON home.id = m.equipo_local_id
             JOIN equipos away ON away.id = m.equipo_visitante_id
             WHERE m.jugado = 1
             ORDER BY m.fecha_juego DESC, m.id DESC'
        );
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM partidos')->fetchColumn();
    }

    public function save(
        int $localTeamId,
        int $visitorTeamId,
        int $localScore,
        int $visitorScore,
        string $playedOn,
        ?string $playedAt = null,
        ?string $venue = null
    ): void
    {
        $this->run(
            'INSERT INTO partidos (equipo_local_id, equipo_visitante_id, puntos_local, puntos_visitante, fecha_juego, hora_juego, sede, jugado)
             VALUES (:local_team_id, :visitor_team_id, :local_score, :visitor_score, :played_on, :played_at, :venue, 1)',
            [
                'local_team_id' => $localTeamId,
                'visitor_team_id' => $visitorTeamId,
                'local_score' => $localScore,
                'visitor_score' => $visitorScore,
                'played_on' => $playedOn,
                'played_at' => $playedAt,
                'venue' => $venue,
            ]
        );
    }

    public function schedule(
        int $localTeamId,
        int $visitorTeamId,
        string $playedOn,
        ?string $playedAt = null,
        ?string $venue = null,
        bool $played = false,
        int $localScore = 0,
        int $visitorScore = 0
    ): void {
        $this->run(
            'INSERT INTO partidos (equipo_local_id, equipo_visitante_id, puntos_local, puntos_visitante, fecha_juego, hora_juego, sede, jugado)
             VALUES (:local_team_id, :visitor_team_id, :local_score, :visitor_score, :played_on, :played_at, :venue, :played)',
            [
                'local_team_id' => $localTeamId,
                'visitor_team_id' => $visitorTeamId,
                'local_score' => $played ? $localScore : 0,
                'visitor_score' => $played ? $visitorScore : 0,
                'played_on' => $playedOn,
                'played_at' => $playedAt,
                'venue' => $venue,
                'played' => $played ? 1 : 0,
            ]
        );
    }

    public function recordResult(int $id, int $localScore, int $visitorScore): void
    {
        $this->run(
            'UPDATE partidos SET puntos_local = :local_score, puntos_visitante = :visitor_score, jugado = 1 WHERE id = :id',
            ['id' => $id, 'local_score' => $localScore, 'visitor_score' => $visitorScore]
        );
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM partidos WHERE id = :id', ['id' => $id]);
    }
}
