<?php

declare(strict_types=1);

namespace Liga\Http\Requests;

use Liga\Support\ValidationException;

final class StoreJugadorRequest extends Request
{
    public function validated(): array
    {
        $id = ($this->input['id'] ?? '') === '' ? null : $this->integer('id', 'El jugador', 1);
        $teamId = $this->integer('team_id', 'El equipo', 1);
        $team = $this->pdo->prepare('SELECT 1 FROM teams WHERE id = :id');
        $team->execute(['id' => $teamId]);
        if (!$team->fetchColumn()) {
            throw new ValidationException('Selecciona un equipo existente.');
        }

        $name = $this->requiredText('name', 'El nombre del jugador', 150);
        $position = $this->requiredText('position', 'La posición', 80);
        $number = $this->integer('number', 'El número de camiseta', 0);
        return compact('id', 'teamId', 'name', 'position', 'number');
    }
}
