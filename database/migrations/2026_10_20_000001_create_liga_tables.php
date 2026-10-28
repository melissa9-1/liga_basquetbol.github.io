<?php

return static function (PDO $pdo): void {
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS usuarios (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre_usuario TEXT NOT NULL UNIQUE,
                correo TEXT,
                contrasena TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS equipos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nombre TEXT NOT NULL,
                ciudad TEXT NOT NULL,
                url_logo TEXT,
                entrenador TEXT,
                anio_fundacion INTEGER
            );

            CREATE TABLE IF NOT EXISTS jugadores (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                equipo_id INTEGER NOT NULL,
                nombre TEXT NOT NULL,
                posicion TEXT NOT NULL,
                numero INTEGER NOT NULL CHECK(numero > 0),
                estatura REAL NULL,
                fecha_nacimiento TEXT NULL,
                FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS partidos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                equipo_local_id INTEGER NOT NULL,
                equipo_visitante_id INTEGER NOT NULL,
                puntos_local INTEGER NOT NULL DEFAULT 0,
                puntos_visitante INTEGER NOT NULL DEFAULT 0,
                fecha_juego TEXT NOT NULL,
                hora_juego TEXT,
                sede TEXT,
                jugado INTEGER NOT NULL DEFAULT 1,
                CHECK(equipo_local_id <> equipo_visitante_id),
                CHECK(puntos_local >= 0),
                CHECK(puntos_visitante >= 0),
                FOREIGN KEY (equipo_local_id) REFERENCES equipos(id) ON DELETE CASCADE,
                FOREIGN KEY (equipo_visitante_id) REFERENCES equipos(id) ON DELETE CASCADE
            );
        ");
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
            correo VARCHAR(254) NULL,
            contrasena VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB;

        CREATE TABLE IF NOT EXISTS equipos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(150) NOT NULL,
            ciudad VARCHAR(150) NOT NULL,
            url_logo TEXT NULL,
            entrenador VARCHAR(150) NULL,
            anio_fundacion SMALLINT UNSIGNED NULL
        ) ENGINE=InnoDB;

        CREATE TABLE IF NOT EXISTS jugadores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            equipo_id INT NOT NULL,
            nombre VARCHAR(150) NOT NULL,
            posicion VARCHAR(80) NOT NULL,
            numero INT NOT NULL,
            estatura DECIMAL(5,2) NULL,
            fecha_nacimiento DATE NULL,
            CONSTRAINT fk_jugadores_equipo FOREIGN KEY (equipo_id) REFERENCES equipos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;

        CREATE TABLE IF NOT EXISTS partidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            equipo_local_id INT NOT NULL,
            equipo_visitante_id INT NOT NULL,
            puntos_local INT NOT NULL DEFAULT 0,
            puntos_visitante INT NOT NULL DEFAULT 0,
            fecha_juego DATE NOT NULL,
            hora_juego TIME NULL,
            sede VARCHAR(200) NULL,
            jugado TINYINT(1) NOT NULL DEFAULT 1,
            CONSTRAINT fk_partidos_local FOREIGN KEY (equipo_local_id) REFERENCES equipos(id) ON DELETE CASCADE,
            CONSTRAINT fk_partidos_visitante FOREIGN KEY (equipo_visitante_id) REFERENCES equipos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");
};
