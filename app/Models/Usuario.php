<?php

declare(strict_types=1);

namespace Liga\Models;

final class User extends Model
{
    public function findByUsername(string $username): ?array
    {
        return $this->fetchOne(
            'SELECT id, nombre_usuario AS username, contrasena AS password, rol AS role
             FROM usuarios WHERE nombre_usuario = :username LIMIT 1',
            [
                'username' => $username,
            ]
        );
    }

    public function create(string $username, string $email, string $password): void
    {
        $this->run(
            'INSERT INTO usuarios (nombre_usuario, correo, contrasena) VALUES (:username, :email, :password)',
            [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]
        );
    }
}
