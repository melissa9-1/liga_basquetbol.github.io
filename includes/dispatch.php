<?php

declare(strict_types=1);

$GLOBALS['liga_pdo'] = require_once __DIR__ . '/init.php';
$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$_SERVER['SCRIPT_NAME'] = rtrim(dirname(dirname($scriptName)), '/') . '/index.php';

function dispatch_module(string $route): void
{
    $pdo = $GLOBALS['liga_pdo'];
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method === 'POST') {
        validate_csrf();
    }

    $routes = require dirname(__DIR__) . '/routes/web.php';
    $handler = $routes[$method][$route] ?? null;
    if ($handler === null) {
        http_response_code(404);
        echo 'Página no encontrada.';
        return;
    }

    [$controllerClass, $action] = $handler;
    (new $controllerClass($pdo))->{$action}();
}
