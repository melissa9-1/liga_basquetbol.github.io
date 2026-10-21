<?php

declare(strict_types=1);

namespace Liga\Http\Controllers;

use Liga\Models\Equipo;
use Liga\Models\Partido;

final class EstadisticaController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $matches = (new Partido($this->pdo))->recent(5);
        $this->render('estadisticas/index', [
            'standings' => $this->standings(),
            'latestMatches' => $matches,
        ]);
    }

    public function standings(): array
    {
        $teams = (new Equipo($this->pdo))->all();
        $rows = $this->pdo->query(
            'SELECT local_team_id, visitor_team_id, local_score, visitor_score FROM matches'
        )->fetchAll();

        $standings = [];
        foreach ($teams as $team) {
            $teamId = (int) $team['id'];
            $entry = [
                'id' => $teamId,
                'name' => $team['name'],
                'city' => $team['city'],
                'games' => 0,
                'wins' => 0,
                'losses' => 0,
                'points_for' => 0,
                'points_against' => 0,
                'difference' => 0,
            ];

            foreach ($rows as $match) {
                $isLocal = (int) $match['local_team_id'] === $teamId;
                if (!$isLocal && (int) $match['visitor_team_id'] !== $teamId) {
                    continue;
                }

                $pointsFor = (int) ($isLocal ? $match['local_score'] : $match['visitor_score']);
                $pointsAgainst = (int) ($isLocal ? $match['visitor_score'] : $match['local_score']);
                $entry['games']++;
                $entry['points_for'] += $pointsFor;
                $entry['points_against'] += $pointsAgainst;
                if ($pointsFor > $pointsAgainst) {
                    $entry['wins']++;
                } else {
                    $entry['losses']++;
                }
            }

            $entry['difference'] = $entry['points_for'] - $entry['points_against'];
            $standings[] = $entry;
        }

        usort($standings, static function (array $a, array $b): int {
            return ($b['wins'] <=> $a['wins'])
                ?: ($b['difference'] <=> $a['difference'])
                ?: strcmp($a['name'], $b['name']);
        });

        return $standings;
    }
}
