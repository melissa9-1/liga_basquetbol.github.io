<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $columns = $pdo->query('PRAGMA table_info(matches)')->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('played', $columns, true)) {
            $pdo->exec('ALTER TABLE matches ADD COLUMN played INTEGER NOT NULL DEFAULT 1');
        }
        return;
    }

    if ($driver === 'mysql') {
        $column = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
        );
        $column->execute(['table' => 'matches', 'column' => 'played']);
        if ((int) $column->fetchColumn() === 0) {
            $pdo->exec('ALTER TABLE matches ADD COLUMN played TINYINT(1) NOT NULL DEFAULT 1');
        }
        return;
    }

    throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
};
