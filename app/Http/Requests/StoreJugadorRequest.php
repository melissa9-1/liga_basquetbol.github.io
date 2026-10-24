<?php

declare(strict_types=1);

namespace Liga\Http\Requests;

use DateTimeImmutable;
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
        $height = null;
        if (($this->input['height'] ?? '') !== '') {
            $heightInput = $this->input['height'];
            if (!is_string($heightInput) || !is_numeric($heightInput)) {
                throw new ValidationException('La estatura debe ser un número válido en centímetros.');
            }
            $height = (float) $heightInput;
            if ($height < 50 || $height > 300) {
                throw new ValidationException('La estatura debe estar entre 50 y 300 centímetros.');
            }
        }

        $birthDate = null;
        if (($this->input['birth_date'] ?? '') !== '') {
            $birthDateInput = $this->input['birth_date'];
            $parsedBirthDate = is_string($birthDateInput)
                ? DateTimeImmutable::createFromFormat('!Y-m-d', $birthDateInput)
                : false;
            if (
                $parsedBirthDate === false
                || $parsedBirthDate->format('Y-m-d') !== $birthDateInput
                || $parsedBirthDate > new DateTimeImmutable('today')
            ) {
                throw new ValidationException('La fecha de nacimiento debe ser válida y no puede ser futura.');
            }
            $birthDate = $birthDateInput;
        }

        return compact('id', 'teamId', 'name', 'position', 'number', 'height', 'birthDate');
    }
}
