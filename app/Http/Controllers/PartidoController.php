<?php

declare(strict_types=1);

namespace Liga\Http\Controllers;

use Liga\Http\Requests\StorePartidoRequest;
use Liga\Http\Requests\StoreResultadoRequest;
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

    public function results(): void
    {
        $this->requireLogin();
        $this->render('partidos/resultados', ['matches' => (new Partido($this->pdo))->all()]);
    }

    public function save(): void
    {
        $this->requireLogin();
        try {
            $data = (new StorePartidoRequest($_POST, $this->pdo))->validated();
        } catch (ValidationException $exception) {
            handle_validation($exception, '/partidos/index.php');
        }

        (new Partido($this->pdo))->schedule(
            $data['localTeamId'],
            $data['visitorTeamId'],
            $data['playedOn']
        );
        flash('success', 'Partido programado correctamente.');
        redirect('/partidos/index.php');
    }

    public function saveResult(): void
    {
        $this->requireLogin();
        try {
            $data = (new StoreResultadoRequest($_POST, $this->pdo))->validated();
        } catch (ValidationException $exception) {
            handle_validation($exception, '/partidos/resultado.php');
        }

        (new Partido($this->pdo))->recordResult(
            $data['matchId'],
            $data['localScore'],
            $data['visitorScore']
        );
        flash('success', 'Marcador registrado correctamente.');
        redirect('/partidos/resultado.php');
    }

    public function delete(): void
    {
        $this->requireLogin();
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            flash('error', 'El partido seleccionado no es válido.');
            redirect('/partidos/index.php');
        }

        (new Partido($this->pdo))->delete($id);
        flash('success', 'Partido eliminado.');
        redirect('/partidos/index.php');
    }
}
