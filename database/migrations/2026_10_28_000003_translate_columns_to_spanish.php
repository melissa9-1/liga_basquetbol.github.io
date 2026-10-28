<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $getColumns = static function (string $table) use ($pdo): array {
            return $pdo->query("PRAGMA table_info(\"{$table}\")")->fetchAll(PDO::FETCH_COLUMN, 1);
        };
    } elseif ($driver === 'mysql') {
        $getColumns = static function (string $table) use ($pdo): array {
            $statement = $pdo->prepare(
                'SELECT column_name FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = :table'
            );
            $statement->execute(['table' => $table]);
            return $statement->fetchAll(PDO::FETCH_COLUMN);
        };
    } else {
        throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
    }

    $renames = [
        'usuarios' => [
            'username' => 'nombre_usuario',
            'email' => 'correo',
            'password' => 'contrasena',
            'role' => 'rol',
        ],
        'equipos' => [
            'name' => 'nombre',
            'city' => 'ciudad',
            'logo_url' => 'url_logo',
            'coach' => 'entrenador',
            'founded_year' => 'anio_fundacion',
        ],
        'jugadores' => [
            'team_id' => 'equipo_id',
            'name' => 'nombre',
            'position' => 'posicion',
            'number' => 'numero',
            'height' => 'estatura',
            'birth_date' => 'fecha_nacimiento',
        ],
        'partidos' => [
            'local_team_id' => 'equipo_local_id',
            'visitor_team_id' => 'equipo_visitante_id',
            'local_score' => 'puntos_local',
            'visitor_score' => 'puntos_visitante',
            'played_on' => 'fecha_juego',
            'played_at' => 'hora_juego',
            'venue' => 'sede',
            'played' => 'jugado',
        ],
    ];
    $mysqlDefinitions = [
        'usuarios' => [
            'username' => 'VARCHAR(100) NOT NULL',
            'email' => 'VARCHAR(254) NULL',
            'password' => 'VARCHAR(255) NOT NULL',
            'role' => "VARCHAR(20) NOT NULL DEFAULT 'usuario'",
        ],
        'equipos' => [
            'name' => 'VARCHAR(150) NOT NULL',
            'city' => 'VARCHAR(150) NOT NULL',
            'logo_url' => 'TEXT NULL',
            'coach' => 'VARCHAR(150) NULL',
            'founded_year' => 'SMALLINT UNSIGNED NULL',
        ],
        'jugadores' => [
            'team_id' => 'INT NOT NULL',
            'name' => 'VARCHAR(150) NOT NULL',
            'position' => 'VARCHAR(80) NOT NULL',
            'number' => 'INT NOT NULL',
            'height' => 'DECIMAL(5,2) NULL',
            'birth_date' => 'DATE NULL',
        ],
        'partidos' => [
            'local_team_id' => 'INT NOT NULL',
            'visitor_team_id' => 'INT NOT NULL',
            'local_score' => 'INT NOT NULL DEFAULT 0',
            'visitor_score' => 'INT NOT NULL DEFAULT 0',
            'played_on' => 'DATE NOT NULL',
            'played_at' => 'TIME NULL',
            'venue' => 'VARCHAR(200) NULL',
            'played' => 'TINYINT(1) NOT NULL DEFAULT 1',
        ],
    ];

    foreach ($renames as $table => $columns) {
        $existingColumns = $getColumns($table);
        if ($existingColumns === []) {
            throw new RuntimeException("No existe la tabla {$table} para traducir sus columnas.");
        }

        foreach ($columns as $oldName => $newName) {
            $hasOldName = in_array($oldName, $existingColumns, true);
            $hasNewName = in_array($newName, $existingColumns, true);

            if ($hasOldName && $hasNewName) {
                throw new RuntimeException(
                    "La tabla {$table} contiene las columnas {$oldName} y {$newName}; revisa los datos antes de continuar."
                );
            }

            if (!$hasOldName) {
                continue;
            }

            if ($driver === 'sqlite') {
                $pdo->exec("ALTER TABLE \"{$table}\" RENAME COLUMN \"{$oldName}\" TO \"{$newName}\"");
            } else {
                $definition = $mysqlDefinitions[$table][$oldName];
                $pdo->exec("ALTER TABLE `{$table}` CHANGE COLUMN `{$oldName}` `{$newName}` {$definition}");
            }

            $existingColumns[array_search($oldName, $existingColumns, true)] = $newName;
        }
    }

    $usuariosColumns = $getColumns('usuarios');
    if (in_array('rol', $usuariosColumns, true)) {
        $pdo->exec(
            "UPDATE usuarios SET rol = CASE rol
                WHEN 'admin' THEN 'administrador'
                WHEN 'user' THEN 'usuario'
                ELSE rol
            END"
        );
    }
};
