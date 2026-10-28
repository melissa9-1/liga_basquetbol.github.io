<?php

declare(strict_types=1);

namespace Liga\Http\Controllers;

use Liga\Models\Equipo;
use Liga\Models\Jugador;
use Liga\Models\Partido;

final class HomeController extends Controller
{
    public function index(): void
    {
        $equipos = new Equipo($this->pdo);
        $jugadores = new Jugador($this->pdo);
        $partidos = new Partido($this->pdo);
        $loggedUser = $this->currentUser();
        $this->render('inicio', [
            'teams' => $equipos->all(),
            'matches' => $partidos->recent(4),
            'standings' => (new EstadisticaController($this->pdo))->standings(),
            'playerCount' => $jugadores->count(),
            'matchCount' => $partidos->count(),
            'loggedUser' => $loggedUser,
        ]);
    }
}
