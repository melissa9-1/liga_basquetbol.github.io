<?php

return static function (PDO $pdo): void {
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS teams (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                city TEXT NOT NULL
            );

            CREATE TABLE IF NOT EXISTS players (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                team_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                position TEXT NOT NULL,
                number INTEGER NOT NULL CHECK(number > 0),
                FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS matches (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                local_team_id INTEGER NOT NULL,
                visitor_team_id INTEGER NOT NULL,
                local_score INTEGER NOT NULL DEFAULT 0,
                visitor_score INTEGER NOT NULL DEFAULT 0,
                played_on TEXT NOT NULL,
                played INTEGER NOT NULL DEFAULT 1,
                CHECK(local_team_id <> visitor_team_id),
                CHECK(local_score >= 0),
                CHECK(visitor_score >= 0),
                FOREIGN KEY (local_team_id) REFERENCES teams(id) ON DELETE CASCADE,
                FOREIGN KEY (visitor_team_id) REFERENCES teams(id) ON DELETE CASCADE
            );
        ");
        return;
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
            played TINYINT(1) NOT NULL DEFAULT 1,
            CONSTRAINT fk_matches_local FOREIGN KEY (local_team_id) REFERENCES teams(id) ON DELETE CASCADE,
            CONSTRAINT fk_matches_visitor FOREIGN KEY (visitor_team_id) REFERENCES teams(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");
};
