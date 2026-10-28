<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver !== 'sqlite' && $driver !== 'mysql') {
        throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
    }

    if ($driver === 'sqlite') {
        $columns = $pdo->query('PRAGMA table_info(usuarios)')->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('rol', $columns, true) && !in_array('role', $columns, true)) {
            $pdo->exec("ALTER TABLE usuarios ADD COLUMN rol TEXT NOT NULL DEFAULT 'usuario'");
        }
        return;
    }

    if ($driver === 'mysql') {
        $column = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
        );
        $column->execute(['table' => 'usuarios', 'column' => 'rol']);
        $hasSpanishRole = (int) $column->fetchColumn() > 0;
        $column->execute(['table' => 'usuarios', 'column' => 'role']);
        $hasLegacyRole = (int) $column->fetchColumn() > 0;
        if (!$hasSpanishRole && !$hasLegacyRole) {
            $pdo->exec("ALTER TABLE usuarios ADD COLUMN rol VARCHAR(20) NOT NULL DEFAULT 'usuario'");
        }
        return;
    }
};
