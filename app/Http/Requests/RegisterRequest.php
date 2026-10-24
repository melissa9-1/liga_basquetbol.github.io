<?php

declare(strict_types=1);

namespace Liga\Http\Requests;

use Liga\Support\ValidationException;

final class RegisterRequest extends Request
{
    public function validated(): array
    {
        $username = $this->requiredText('username', 'El usuario', 100);
        $email = $this->requiredText('email', 'El correo electrónico', 254);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ValidationException('Ingresa un correo electrónico válido.');
        }

        $password = $this->input['password'] ?? null;
        if (!is_string($password) || strlen($password) < 4) {
            throw new ValidationException('La contraseña debe tener al menos 4 caracteres.');
        }

        return compact('username', 'email', 'password');
    }
}
