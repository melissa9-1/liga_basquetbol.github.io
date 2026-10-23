<?php

declare(strict_types=1);

namespace Liga\Support;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $host = getenv('DB_HOST') ?: 'localhost';
        $database = getenv('DB_DATABASE') ?: 'liga_basquetbol';
        $username = getenv('DB_USERNAME') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';

        try {
            self::$connection = new PDO(
                "mysql:host={$host};dbname={$database};charset=utf8mb4",
                $username,
                $password,
                $options
            );
        } catch (PDOException $exception) {
            try {
                $server = new PDO("mysql:host={$host};charset=utf8mb4", $username, $password, $options);
                $safeDatabase = str_replace('`', '``', $database);
                $server->exec("CREATE DATABASE IF NOT EXISTS `{$safeDatabase}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                self::$connection = new PDO(
                    "mysql:host={$host};dbname={$database};charset=utf8mb4",
                    $username,
                    $password,
                    $options
                );
            } catch (PDOException $connectionException) {
                throw new RuntimeException(
                    'No se pudo conectar a MySQL. Activa MySQL en XAMPP y revisa DB_HOST, DB_DATABASE, DB_USERNAME y DB_PASSWORD.',
                    0,
                    $connectionException
                );
            }
        }

        return self::$connection;
    }
}
