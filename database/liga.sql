CREATE DATABASE IF NOT EXISTS liga_basquetbol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE liga_basquetbol;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE,
    correo VARCHAR(254) NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL DEFAULT 'usuario'
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