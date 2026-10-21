<?php

declare(strict_types=1);

namespace Liga\Http\Requests;

use Liga\Support\ValidationException;

final class StorePartidoRequest extends Request
{
    public function validated(): array
    {
        $localTeamId = $this->integer('local_team_id', 'El equipo local', 1);
        $visitorTeamId = $this->integer('visitor_team_id', 'El equipo visitante', 1);
        if ($localTeamId === $visitorTeamId) {
            throw new ValidationException('Selecciona dos equipos distintos.');
        }

        $team = $this->pdo->prepare('SELECT COUNT(*) FROM teams WHERE id IN (:local, :visitor)');
        $team->execute(['local' => $localTeamId, 'visitor' => $visitorTeamId]);
        if ((int) $team->fetchColumn() !== 2) {
            throw new ValidationException('Los dos equipos seleccionados deben existir.');
        }

        $localScore = $this->integer('local_score', 'El marcador local');
        $visitorScore = $this->integer('visitor_score', 'El marcador visitante');
        $playedOn = $this->input['played_on'] ?? null;
        if (!is_string($playedOn)) {
            throw new ValidationException('La fecha del partido no es válida.');
        }
        $playedOn = trim($playedOn);
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $playedOn);
        $dateErrors = \DateTimeImmutable::getLastErrors();
        if (
            $date === false
            || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
            || $date->format('Y-m-d') !== $playedOn
        ) {
            throw new ValidationException('La fecha del partido no es válida.');
        }

        return compact('localTeamId', 'visitorTeamId', 'localScore', 'visitorScore', 'playedOn');
    }
}
