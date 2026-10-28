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
        $file = dirname(__DIR__, 2) . '/..' . '/resources/vistas/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("No existe la vista {$view}.");
        }

        $data['successMessage'] = consume_flash('success');
        $data['errorMessage'] = consume_flash('error');
        extract($data, EXTR_SKIP);
        require $file;
    }

    protected function currentUser(): ?array
    {
        $user = current_user();
        if ($user === null) {
            return null;
        }

        $statement = $this->pdo->prepare(
            'SELECT id, nombre_usuario AS username, rol AS role FROM usuarios WHERE id = :id LIMIT 1'
        );
        $statement->execute(['id' => (int) ($user['id'] ?? 0)]);
        $account = $statement->fetch();
        if (!is_array($account)) {
            unset($_SESSION['user']);
            return null;
        }

        $_SESSION['user'] = [
            'id' => (int) $account['id'],
            'username' => $account['username'],
            'role' => $account['role'],
        ];

        return $_SESSION['user'];
    }

    protected function requireAdmin(): void
    {
        $user = $this->currentUser();
        if ($user === null) {
            flash('error', 'Inicia sesión para acceder al panel de administración.');
            redirect('/');
        }

        if ($user['role'] !== 'administrador') {
            flash('error', 'No tienes permisos para administrar la liga.');
            redirect('/');
        }
    }
}
