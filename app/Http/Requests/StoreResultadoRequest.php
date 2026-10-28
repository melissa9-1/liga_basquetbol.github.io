<?php

declare(strict_types=1);

namespace Liga\Http\Requests;

use Liga\Support\ValidationException;

final class StoreResultadoRequest extends Request
{
    public function validated(): array
    {
        $matchId = $this->integer('match_id', 'El partido', 1);
        $match = $this->pdo->prepare('SELECT 1 FROM partidos WHERE id = :id');
        $match->execute(['id' => $matchId]);
        if (!$match->fetchColumn()) {
            throw new ValidationException('El partido seleccionado no existe.');
        }

        $localScore = $this->integer('local_score', 'El marcador local');
        $visitorScore = $this->integer('visitor_score', 'El marcador visitante');

        return compact('matchId', 'localScore', 'visitorScore');
    }
}
