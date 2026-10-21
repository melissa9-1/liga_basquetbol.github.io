<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'Liga\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = __DIR__ . '/app/' . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

require_once __DIR__ . '/app/Support/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pdo = Liga\Support\Database::connection();
Liga\Support\MigrationRunner::run($pdo, __DIR__ . '/database/migrations');
