<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $columns = $pdo->query('PRAGMA table_info(players)')->fetchAll(PDO::FETCH_COLUMN, 1);
        if ($columns === []) {
            throw new RuntimeException('No existe la tabla players para agregar los detalles de los jugadores.');
        }

        $definitions = [
            'height' => 'REAL NULL',
            'birth_date' => 'TEXT NULL',
        ];
        foreach ($definitions as $name => $definition) {
            if (!in_array($name, $columns, true)) {
                $pdo->exec("ALTER TABLE players ADD COLUMN {$name} {$definition}");
            }
        }
        return;
    }

    if ($driver === 'mysql') {
        $column = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
        );
        $definitions = [
            'height' => 'DECIMAL(5,2) NULL',
            'birth_date' => 'DATE NULL',
        ];
        foreach ($definitions as $name => $definition) {
            $column->execute(['table' => 'players', 'column' => $name]);
            if ((int) $column->fetchColumn() === 0) {
                $pdo->exec("ALTER TABLE players ADD COLUMN {$name} {$definition}");
            }
        }
        return;
    }

    throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
};
