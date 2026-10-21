<?php

declare(strict_types=1);

namespace Liga\Http\Controllers;

use PDO;

abstract class Controller
{
    public function __construct(protected PDO $pdo)
    {
    }

    protected function render(string $view, array $data = []): void
    {
        $file = dirname(__DIR__, 2) . '/..' . '/resources/views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("No existe la vista {$view}.");
        }

        $data['successMessage'] = consume_flash('success');
        $data['errorMessage'] = consume_flash('error');
        extract($data, EXTR_SKIP);
        require $file;
    }

    protected function requireLogin(): void
    {
        if (current_user() === null) {
            flash('error', 'Inicia sesión para acceder al panel de administración.');
            redirect('/');
        }
    }
}
