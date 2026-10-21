<?php

declare(strict_types=1);

namespace Liga\Http\Requests;

use Liga\Support\ValidationException;

final class RegisterRequest extends Request
{
    public function validated(): array
    {
        $username = $this->requiredText('username', 'El usuario', 100);
        $password = $this->input['password'] ?? null;
        if (!is_string($password) || strlen($password) < 4) {
            throw new ValidationException('La contraseña debe tener al menos 4 caracteres.');
        }

        return compact('username', 'password');
    }
}
