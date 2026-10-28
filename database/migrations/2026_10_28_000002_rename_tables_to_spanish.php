<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $tableExists = $pdo->prepare(
            "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = :table"
        );
    } elseif ($driver === 'mysql') {
        $tableExists = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = :table'
        );
    } else {
        throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
    }

    foreach ([
        'users' => 'usuarios',
        'teams' => 'equipos',
        'players' => 'jugadores',
        'matches' => 'partidos',
    ] as $oldName => $newName) {
        $tableExists->execute(['table' => $oldName]);
        $oldExists = (int) $tableExists->fetchColumn() > 0;
        $tableExists->execute(['table' => $newName]);
        $newExists = (int) $tableExists->fetchColumn() > 0;

        if ($oldExists && $newExists) {
            throw new RuntimeException(
                "Existen las tablas {$oldName} y {$newName}; revisa los datos antes de renombrarlas."
            );
        }

        if (!$oldExists && !$newExists) {
            throw new RuntimeException("No existe la tabla {$oldName} ni {$newName} para aplicar el renombre.");
        }

        if (!$oldExists) {
            continue;
        }

        if ($driver === 'sqlite') {
            $pdo->exec("ALTER TABLE \"{$oldName}\" RENAME TO \"{$newName}\"");
        } else {
            $pdo->exec("RENAME TABLE `{$oldName}` TO `{$newName}`");
        }
    }
};
