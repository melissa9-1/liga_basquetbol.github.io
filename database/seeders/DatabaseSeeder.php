<?php

declare(strict_types=1);

use Liga\Models\Equipo;

final class DatabaseSeeder
{
    public function run(PDO $pdo): void
    {
        if ((new Equipo($pdo))->count() > 0) {
            return;
        }

        $insert = $pdo->prepare('INSERT INTO teams (name, city) VALUES (:name, :city)');
        foreach ([
            ['name' => 'Halcones', 'city' => 'Monterrey'],
            ['name' => 'Titanes', 'city' => 'Guadalajara'],
            ['name' => 'Leones', 'city' => 'Ciudad de México'],
        ] as $team) {
            $insert->execute($team);
        }
    }
}
