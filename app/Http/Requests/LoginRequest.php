<?php

declare(strict_types=1);

namespace Liga\Http\Requests;

final class LoginRequest extends Request
{
    public function validated(): array
    {
        $username = $this->requiredText('username', 'El usuario', 100);
        $password = $this->input['password'] ?? null;
        if (!is_string($password) || $password === '') {
            throw new \Liga\Support\ValidationException('La contraseña es obligatoria.');
        }

        return compact('username', 'password');
    }
}
