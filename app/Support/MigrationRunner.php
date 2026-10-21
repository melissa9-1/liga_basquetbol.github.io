<?php

declare(strict_types=1);

namespace Liga\Support;

use PDO;
use RuntimeException;

final class MigrationRunner
{
    public static function run(PDO $pdo, string $directory): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                ran_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB'
        );

        $files = glob($directory . DIRECTORY_SEPARATOR . '*.php');
        if ($files === false) {
            throw new RuntimeException('No se pudo leer el directorio de migraciones.');
        }

        sort($files, SORT_STRING);
        foreach ($files as $file) {
            $name = basename($file);
            $check = $pdo->prepare('SELECT 1 FROM migrations WHERE migration = :migration LIMIT 1');
            $check->execute(['migration' => $name]);
            if ($check->fetchColumn()) {
                continue;
            }

            $migration = require $file;
            if (!is_callable($migration)) {
                throw new RuntimeException("La migración {$name} no devuelve una función ejecutable.");
            }

            $migration($pdo);
            $record = $pdo->prepare('INSERT INTO migrations (migration) VALUES (:migration)');
            $record->execute(['migration' => $name]);
        }
    }
}
