<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $columns = $pdo->query('PRAGMA table_info(partidos)')->fetchAll(PDO::FETCH_COLUMN, 1);
        if ($columns === []) {
            throw new RuntimeException('No existe la tabla partidos para agregar los detalles de los partidos.');
        }

        $definitions = [
            'hora_juego' => 'TEXT NULL',
            'sede' => 'TEXT NULL',
        ];
        foreach ($definitions as $name => $definition) {
            if (!in_array($name, $columns, true)) {
                $pdo->exec("ALTER TABLE partidos ADD COLUMN {$name} {$definition}");
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
            'hora_juego' => 'TIME NULL',
            'sede' => 'VARCHAR(200) NULL',
        ];
        foreach ($definitions as $name => $definition) {
            $column->execute(['table' => 'partidos', 'column' => $name]);
            if ((int) $column->fetchColumn() === 0) {
                $pdo->exec("ALTER TABLE partidos ADD COLUMN {$name} {$definition}");
            }
        }
        return;
    }

    throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
};
