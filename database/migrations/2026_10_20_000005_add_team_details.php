<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $columns = $pdo->query('PRAGMA table_info(equipos)')->fetchAll(PDO::FETCH_COLUMN, 1);
        if ($columns === []) {
            throw new RuntimeException('No existe la tabla equipos para agregar los detalles de los equipos.');
        }

        $definitions = [
            'url_logo' => 'TEXT NULL',
            'entrenador' => 'TEXT NULL',
            'anio_fundacion' => 'INTEGER NULL',
        ];
        foreach ($definitions as $name => $definition) {
            if (!in_array($name, $columns, true)) {
                $pdo->exec("ALTER TABLE equipos ADD COLUMN {$name} {$definition}");
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
            'url_logo' => 'TEXT NULL',
            'entrenador' => 'VARCHAR(150) NULL',
            'anio_fundacion' => 'SMALLINT UNSIGNED NULL',
        ];
        foreach ($definitions as $name => $definition) {
            $column->execute(['table' => 'equipos', 'column' => $name]);
            if ((int) $column->fetchColumn() === 0) {
                $pdo->exec("ALTER TABLE equipos ADD COLUMN {$name} {$definition}");
            }
        }
        return;
    }

    throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
};
