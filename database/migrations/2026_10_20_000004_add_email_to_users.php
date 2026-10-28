<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $columns = $pdo->query('PRAGMA table_info(usuarios)')->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('correo', $columns, true)) {
            $pdo->exec('ALTER TABLE usuarios ADD COLUMN correo TEXT');
        }

        $hasUniqueCorreoIndex = false;
        $indexes = $pdo->query('PRAGMA index_list(usuarios)')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($indexes as $index) {
            if ((int) $index['unique'] !== 1) {
                continue;
            }

            $indexName = str_replace('"', '""', (string) $index['name']);
            $indexColumns = $pdo->query("PRAGMA index_info(\"{$indexName}\")")->fetchAll(PDO::FETCH_COLUMN, 2);
            if (in_array('correo', $indexColumns, true)) {
                $hasUniqueCorreoIndex = true;
                break;
            }
        }
        if (!$hasUniqueCorreoIndex) {
            $pdo->exec('CREATE UNIQUE INDEX usuarios_correo_unique ON usuarios(correo)');
        }
        return;
    }

    if ($driver === 'mysql') {
        $column = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
        );
        $column->execute(['table' => 'usuarios', 'column' => 'correo']);
        if ((int) $column->fetchColumn() === 0) {
            $pdo->exec('ALTER TABLE usuarios ADD COLUMN correo VARCHAR(254) NULL');
        }

        $index = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = :table
               AND column_name = :column AND non_unique = 0'
        );
        $index->execute(['table' => 'usuarios', 'column' => 'correo']);
        if ((int) $index->fetchColumn() === 0) {
            $pdo->exec('CREATE UNIQUE INDEX usuarios_correo_unique ON usuarios(correo)');
        }
        return;
    }

    throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
};
