<?php
session_start();

$host = 'localhost';
$dbname = 'liga_basquetbol';
$username = 'root';
$password = '';

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, $pdoOptions);
} catch (PDOException $e) {
    try {
        $connection = new PDO("mysql:host={$host};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $connection->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, $pdoOptions);
    } catch (PDOException $ex) {
        throw new RuntimeException('No se pudo conectar a MySQL de XAMPP. Verifica que Apache y MySQL estén activos.');
    }
}

$pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS teams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        city VARCHAR(150) NOT NULL
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS players (
        id INT AUTO_INCREMENT PRIMARY KEY,
        team_id INT NOT NULL,
        name VARCHAR(150) NOT NULL,
        position VARCHAR(80) NOT NULL,
        number INT NOT NULL,
        CONSTRAINT fk_players_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

    CREATE TABLE IF NOT EXISTS matches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        local_team_id INT NOT NULL,
        visitor_team_id INT NOT NULL,
        local_score INT NOT NULL DEFAULT 0,
        visitor_score INT NOT NULL DEFAULT 0,
        played_on DATE NOT NULL,
        CONSTRAINT fk_matches_local FOREIGN KEY (local_team_id) REFERENCES teams(id) ON DELETE CASCADE,
        CONSTRAINT fk_matches_visitor FOREIGN KEY (visitor_team_id) REFERENCES teams(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;
");

foreach ([
    'usuarios' => "INSERT INTO users (id, username, password) SELECT id, usuario, password FROM usuarios WHERE NOT EXISTS (SELECT 1 FROM users u2 WHERE u2.id = usuarios.id OR u2.username = usuarios.usuario) ORDER BY id;",
    'equipos' => "INSERT INTO teams (id, name, city) SELECT id, nombre, ciudad FROM equipos WHERE NOT EXISTS (SELECT 1 FROM teams t2 WHERE t2.id = equipos.id OR (t2.name = equipos.nombre AND t2.city = equipos.ciudad)) ORDER BY id;",
    'jugadores' => "INSERT INTO players (id, team_id, name, position, number) SELECT id, equipo_id, nombre, posicion, numero FROM jugadores WHERE NOT EXISTS (SELECT 1 FROM players p2 WHERE p2.id = jugadores.id OR (p2.team_id = jugadores.equipo_id AND p2.name = jugadores.nombre AND p2.position = jugadores.posicion AND p2.number = jugadores.numero)) ORDER BY id;",
    'partidos' => "INSERT INTO matches (id, local_team_id, visitor_team_id, local_score, visitor_score, played_on) SELECT id, equipo_local_id, equipo_visitante_id, marcador_local, marcador_visitante, fecha FROM partidos WHERE NOT EXISTS (SELECT 1 FROM matches m2 WHERE m2.id = partidos.id OR (m2.local_team_id = partidos.equipo_local_id AND m2.visitor_team_id = partidos.equipo_visitante_id AND m2.local_score = partidos.marcador_local AND m2.visitor_score = partidos.marcador_visitante AND m2.played_on = partidos.fecha)) ORDER BY id;",
] as $legacyTable => $sql) {
    $tableExists = $pdo->query("SHOW TABLES LIKE '{$legacyTable}'")->fetch();
    if ($tableExists) {
        $pdo->exec($sql);
    }
}

return $pdo;
