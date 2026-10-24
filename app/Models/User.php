<?php

declare(strict_types=1);

namespace Liga\Models;

final class User extends Model
{
    public function findByUsername(string $username): ?array
    {
        return $this->fetchOne('SELECT id, username, password FROM users WHERE username = :username LIMIT 1', [
            'username' => $username,
        ]);
    }

    public function create(string $username, string $email, string $password): void
    {
        $this->run(
            'INSERT INTO users (username, email, password) VALUES (:username, :email, :password)',
            [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ]
        );
    }
}
