CREATE DATABASE IF NOT EXISTS liga_basquetbol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE liga_basquetbol;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(254) NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    city VARCHAR(150) NOT NULL,
    logo_url TEXT NULL,
    coach VARCHAR(150) NULL,
    founded_year SMALLINT UNSIGNED NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    position VARCHAR(80) NOT NULL,
    number INT NOT NULL,
    height DECIMAL(5,2) NULL,
    birth_date DATE NULL,
    CONSTRAINT fk_players_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    local_team_id INT NOT NULL,
    visitor_team_id INT NOT NULL,
    local_score INT NOT NULL DEFAULT 0,
    visitor_score INT NOT NULL DEFAULT 0,
    played_on DATE NOT NULL,
    played_at TIME NULL,
    venue VARCHAR(200) NULL,
    played TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_matches_local FOREIGN KEY (local_team_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_matches_visitor FOREIGN KEY (visitor_team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB;
