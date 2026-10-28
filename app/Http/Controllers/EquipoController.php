<?php

declare(strict_types=1);

namespace Liga\Http\Controllers;

use Liga\Http\Requests\StoreEquipoRequest;
use Liga\Models\Equipo;
use Liga\Support\ValidationException;

final class EquipoController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        $model = new Equipo($this->pdo);
        $editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);
        $editingTeam = $editId !== false && $editId > 0 ? $model->find($editId) : null;
        $this->render('equipos/index', ['teams' => $model->all(), 'editingTeam' => $editingTeam]);
    }

    public function save(): void
    {
        $this->requireAdmin();
        try {
            $data = (new StoreEquipoRequest($_POST, $this->pdo))->validated();
        } catch (ValidationException $exception) {
            handle_validation($exception, '/equipos/index.php');
        }

        (new Equipo($this->pdo))->save(
            $data['id'],
            $data['name'],
            $data['city'],
            $data['logoUrl'],
            $data['coach'],
            $data['foundedYear']
        );
        flash('success', $data['id'] === null ? 'Equipo creado correctamente.' : 'Equipo actualizado correctamente.');
        redirect('/equipos/index.php');
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            flash('error', 'El equipo seleccionado no es válido.');
            redirect('/equipos/index.php');
        }

        (new Equipo($this->pdo))->delete($id);
        flash('success', 'Equipo eliminado.');
        redirect('/equipos/index.php');
    }
}
