<?php

declare(strict_types=1);

namespace Liga\Http\Controllers;

use Liga\Http\Requests\LoginRequest;
use Liga\Http\Requests\RegisterRequest;
use Liga\Models\User;
use Liga\Support\ValidationException;
use PDOException;

final class AuthController extends Controller
{
    public function login(): void
    {
        try {
            $data = (new LoginRequest($_POST, $this->pdo))->validated();
        } catch (ValidationException $exception) {
            handle_validation($exception, '/');
        }

        $user = (new User($this->pdo))->findByUsername($data['username']);
        if ($user === null || !password_verify($data['password'], $user['password'])) {
            flash('error', 'Credenciales incorrectas.');
            redirect('/');
        }

        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int) $user['id'], 'username' => $user['username']];
        flash('success', 'Sesión iniciada correctamente.');
        redirect('/');
    }

    public function register(): void
    {
        try {
            $data = (new RegisterRequest($_POST, $this->pdo))->validated();
        } catch (ValidationException $exception) {
            handle_validation($exception, '/');
        }

        try {
            (new User($this->pdo))->create($data['username'], $data['password']);
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }
            flash('error', 'Ese usuario ya existe.');
            redirect('/');
        }

        flash('success', 'Usuario registrado correctamente. Ya puedes iniciar sesión.');
        redirect('/');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $parameters['path'], $parameters['domain'], $parameters['secure'], $parameters['httponly']);
        }
        session_destroy();
        redirect('/');
    }
}
