<?php

declare(strict_types=1);

use Liga\Support\ValidationException;

function h(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_base_path(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $directory = dirname($scriptName);
    return $directory === '/' || $directory === '.' ? '' : rtrim($directory, '/');
}

function url(string $path = '/'): string
{
    return app_base_path() . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    $file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));
    $version = is_file($file) ? (string) filemtime($file) : '';

    return url($path) . ($version !== '' ? '?v=' . rawurlencode($version) : '');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? null) === 'administrador';
}

function flash(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function consume_flash(string $key): ?string
{
    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return is_string($message) ? $message : null;
}

function csrf_token(): string
{
    if (!isset($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . h(csrf_token()) . '">';
}

function validate_csrf(): void
{
    $submitted = $_POST['_token'] ?? null;
    if (!is_string($submitted) || $submitted === '' || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        echo 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.';
        exit;
    }
}

function handle_validation(ValidationException $exception, string $path): never
{
    flash('error', $exception->getMessage());
    redirect($path);
}
