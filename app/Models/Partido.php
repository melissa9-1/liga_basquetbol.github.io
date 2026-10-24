<?php

declare(strict_types=1);

namespace Liga\Models;

final class Partido extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT m.*, home.name AS local_name, home.city AS local_city,
                    away.name AS visitor_name, away.city AS visitor_city
             FROM matches m
             JOIN teams home ON home.id = m.local_team_id
             JOIN teams away ON away.id = m.visitor_team_id
             ORDER BY m.played_on DESC, m.id DESC'
        );
    }

    public function recent(int $limit = 4): array
    {
        $limit = max(1, min(100, $limit));
        return $this->fetchAll(
            'SELECT m.*, home.name AS local_name, home.city AS local_city,
                    away.name AS visitor_name, away.city AS visitor_city
             FROM matches m
             JOIN teams home ON home.id = m.local_team_id
             JOIN teams away ON away.id = m.visitor_team_id
             WHERE m.played = 1
             ORDER BY m.played_on DESC, m.id DESC
             LIMIT ' . $limit
        );
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM matches')->fetchColumn();
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
            'INSERT INTO matches (local_team_id, visitor_team_id, local_score, visitor_score, played_on, played_at, venue, played)
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
            'INSERT INTO matches (local_team_id, visitor_team_id, local_score, visitor_score, played_on, played_at, venue, played)
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
            'UPDATE matches SET local_score = :local_score, visitor_score = :visitor_score, played = 1 WHERE id = :id',
            ['id' => $id, 'local_score' => $localScore, 'visitor_score' => $visitorScore]
        );
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM matches WHERE id = :id', ['id' => $id]);
    }
}
