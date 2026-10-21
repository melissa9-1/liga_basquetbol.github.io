<?php

declare(strict_types=1);

namespace Liga\Http\Controllers;

use Liga\Http\Requests\StorePartidoRequest;
use Liga\Models\Equipo;
use Liga\Models\Partido;
use Liga\Support\ValidationException;

final class PartidoController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->render('partidos/index', [
            'teams' => (new Equipo($this->pdo))->all(),
            'matches' => (new Partido($this->pdo))->all(),
        ]);
    }

    public function save(): void
    {
        $this->requireLogin();
        try {
            $data = (new StorePartidoRequest($_POST, $this->pdo))->validated();
        } catch (ValidationException $exception) {
            handle_validation($exception, '/partidos');
        }

        (new Partido($this->pdo))->save(
            $data['localTeamId'],
            $data['visitorTeamId'],
            $data['localScore'],
            $data['visitorScore'],
            $data['playedOn']
        );
        flash('success', 'Partido registrado correctamente.');
        redirect('/partidos');
    }

    public function delete(): void
    {
        $this->requireLogin();
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            flash('error', 'El partido seleccionado no es válido.');
            redirect('/partidos');
        }

        (new Partido($this->pdo))->delete($id);
        flash('success', 'Partido eliminado.');
        redirect('/partidos');
    }
}
