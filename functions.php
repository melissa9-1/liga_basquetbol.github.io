<?php
require_once __DIR__ . '/db.php';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function requireLogin()
{
    if (!currentUser()) {
        header('Location: index.php');
        exit;
    }
}

function redirect($path)
{
    header('Location: ' . $path);
    exit;
}

function flash($key, $message)
{
    $_SESSION['flash'][$key] = $message;
}

function getFlash($key)
{
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}

function getTeams($pdo)
{
    return $pdo->query('SELECT * FROM teams ORDER BY name ASC')->fetchAll();
}

function getPlayersByTeam($pdo, $teamId)
{
    $stmt = $pdo->prepare('SELECT * FROM players WHERE team_id = :team_id ORDER BY number ASC, name ASC');
    $stmt->execute([':team_id' => $teamId]);
    return $stmt->fetchAll();
}

function getMatches($pdo)
{
    $stmt = $pdo->query(
        'SELECT p.*, el.name AS local_name, el.city AS local_city, ev.name AS visitor_name, ev.city AS visitor_city
         FROM matches p
         JOIN teams el ON el.id = p.local_team_id
         JOIN teams ev ON ev.id = p.visitor_team_id
         ORDER BY p.played_on DESC, p.id DESC'
    );
    return $stmt->fetchAll();
}

function getStandings($pdo)
{
    $teams = getTeams($pdo);
    $standings = [];

    foreach ($teams as $team) {
        $teamId = (int) $team['id'];
        $matches = $pdo->prepare(
            'SELECT local_team_id, visitor_team_id, local_score, visitor_score
             FROM matches
             WHERE local_team_id = :team_id OR visitor_team_id = :team_id'
        );
        $matches->execute([':team_id' => $teamId]);
        $rows = $matches->fetchAll();

        $wins = 0;
        $losses = 0;
        $pointsFor = 0;
        $pointsAgainst = 0;
        $games = 0;

        foreach ($rows as $match) {
            $games++;
            $isLocal = (int) $match['local_team_id'] === $teamId;
            $localScore = (int) $match['local_score'];
            $visitorScore = (int) $match['visitor_score'];

            if ($isLocal) {
                $pointsFor += $localScore;
                $pointsAgainst += $visitorScore;
                if ($localScore > $visitorScore) {
                    $wins++;
                } else {
                    $losses++;
                }
            } else {
                $pointsFor += $visitorScore;
                $pointsAgainst += $localScore;
                if ($visitorScore > $localScore) {
                    $wins++;
                } else {
                    $losses++;
                }
            }
        }

        $standings[] = [
            'id' => $team['id'],
            'name' => $team['name'],
            'city' => $team['city'],
            'games' => $games,
            'wins' => $wins,
            'losses' => $losses,
            'points_for' => $pointsFor,
            'points_against' => $pointsAgainst,
            'difference' => $pointsFor - $pointsAgainst,
        ];
    }

    usort($standings, function ($a, $b) {
        if ($a['wins'] !== $b['wins']) {
            return $b['wins'] <=> $a['wins'];
        }
        if ($a['difference'] !== $b['difference']) {
            return $b['difference'] <=> $a['difference'];
        }
        return strcmp($a['name'], $b['name']);
    });

    return $standings;
}
