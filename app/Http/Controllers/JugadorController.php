<?php

declare(strict_types=1);

namespace Liga\Http\Controllers;

use Liga\Http\Requests\StoreJugadorRequest;
use Liga\Models\Equipo;
use Liga\Models\Jugador;
use Liga\Support\ValidationException;

final class JugadorController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $model = new Jugador($this->pdo);
        $editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);
        $editingPlayer = $editId !== false && $editId > 0 ? $model->find($editId) : null;
        $this->render('jugadores/index', [
            'teams' => (new Equipo($this->pdo))->all(),
            'players' => $model->all(),
            'editingPlayer' => $editingPlayer,
        ]);
    }

    public function save(): void
    {
        $this->requireLogin();
        try {
            $data = (new StoreJugadorRequest($_POST, $this->pdo))->validated();
        } catch (ValidationException $exception) {
            handle_validation($exception, '/jugadores');
        }

        (new Jugador($this->pdo))->save(
            $data['id'],
            $data['teamId'],
            $data['name'],
            $data['position'],
            $data['number']
        );
        flash('success', $data['id'] === null ? 'Jugador agregado.' : 'Jugador actualizado.');
        redirect('/jugadores');
    }

    public function delete(): void
    {
        $this->requireLogin();
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            flash('error', 'El jugador seleccionado no es válido.');
            redirect('/jugadores');
        }

        (new Jugador($this->pdo))->delete($id);
        flash('success', 'Jugador eliminado.');
        redirect('/jugadores');
    }
}
