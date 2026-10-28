<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $columns = $pdo->query('PRAGMA table_info(jugadores)')->fetchAll(PDO::FETCH_COLUMN, 1);
        if ($columns === []) {
            throw new RuntimeException('No existe la tabla jugadores para agregar los detalles de los jugadores.');
        }

        $definitions = [
            'estatura' => 'REAL NULL',
            'fecha_nacimiento' => 'TEXT NULL',
        ];
        foreach ($definitions as $name => $definition) {
            if (!in_array($name, $columns, true)) {
                $pdo->exec("ALTER TABLE jugadores ADD COLUMN {$name} {$definition}");
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
            'estatura' => 'DECIMAL(5,2) NULL',
            'fecha_nacimiento' => 'DATE NULL',
        ];
        foreach ($definitions as $name => $definition) {
            $column->execute(['table' => 'jugadores', 'column' => $name]);
            if ((int) $column->fetchColumn() === 0) {
                $pdo->exec("ALTER TABLE jugadores ADD COLUMN {$name} {$definition}");
            }
        }
        return;
    }

    throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
};
