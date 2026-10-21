<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
$path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$path = is_string($path) && $path !== '' ? $path : '/';
$basePath = app_base_path();

if ($basePath !== '' && ($path === $basePath || str_starts_with($path, $basePath . '/'))) {
    $path = substr($path, strlen($basePath)) ?: '/';
}

$path = '/' . trim($path, '/');
if ($path === '//') {
    $path = '/';
}

if ($method === 'POST') {
    validate_csrf();
}



$routes = require __DIR__ . '/routes/web.php';
$route = $routes[$method][$path] ?? null;
if ($route === null) {
    http_response_code(404);
    echo 'Página no encontrada.';
    exit;
}

[$controllerClass, $action] = $route;
$controller = new $controllerClass($pdo);
$controller->{$action}();
