<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $columns = $pdo->query('PRAGMA table_info(partidos)')->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('jugado', $columns, true)) {
            $pdo->exec('ALTER TABLE partidos ADD COLUMN jugado INTEGER NOT NULL DEFAULT 1');
        }
        return;
    }

    if ($driver === 'mysql') {
        $column = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
        );
        $column->execute(['table' => 'partidos', 'column' => 'jugado']);
        if ((int) $column->fetchColumn() === 0) {
            $pdo->exec('ALTER TABLE partidos ADD COLUMN jugado TINYINT(1) NOT NULL DEFAULT 1');
        }
        return;
    }

    throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
};
