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

    public function create(string $username, string $password): void
    {
        $this->run(
            'INSERT INTO users (username, password) VALUES (:username, :password)',
            ['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT)]
        );
    }
}
