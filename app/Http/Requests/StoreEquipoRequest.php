<?php

declare(strict_types=1);

namespace Liga\Http\Requests;

final class StoreEquipoRequest extends Request
{
    public function validated(): array
    {
        $name = $this->requiredText('name', 'El nombre del equipo', 150);
        $city = $this->requiredText('city', 'La ciudad', 150);
        $id = ($this->input['id'] ?? '') === '' ? null : $this->integer('id', 'El equipo', 1);
        return compact('id', 'name', 'city');
    }
}
