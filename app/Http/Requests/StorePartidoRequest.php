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

        $team = $this->pdo->prepare('SELECT COUNT(*) FROM equipos WHERE id IN (:local, :visitor)');
        $team->execute(['local' => $localTeamId, 'visitor' => $visitorTeamId]);
        if ((int) $team->fetchColumn() !== 2) {
            throw new ValidationException('Los dos equipos seleccionados deben existir.');
        }

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

        $playedAt = $this->input['played_at'] ?? null;
        if (!is_string($playedAt) || preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $playedAt) !== 1) {
            throw new ValidationException('La hora del partido no es válida.');
        }

        $venue = $this->requiredText('venue', 'El lugar del partido', 200);
        $status = $this->input['status'] ?? null;
        if (!is_string($status) || !in_array($status, ['scheduled', 'played'], true)) {
            throw new ValidationException('Selecciona un estado válido para el partido.');
        }

        $played = $status === 'played' ? 1 : 0;
        $localScore = 0;
        $visitorScore = 0;
        if ($played === 1) {
            $localScore = $this->integer('local_score', 'El marcador local');
            $visitorScore = $this->integer('visitor_score', 'El marcador visitante');
        }

        return compact(
            'localTeamId',
            'visitorTeamId',
            'playedOn',
            'playedAt',
            'venue',
            'played',
            'localScore',
            'visitorScore'
        );
    }
}
