<?php

use Liga\Http\Controllers\AuthController;
use Liga\Http\Controllers\EquipoController;
use Liga\Http\Controllers\HomeController;
use Liga\Http\Controllers\JugadorController;
use Liga\Http\Controllers\PartidoController;
use Liga\Http\Controllers\EstadisticaController;

return [
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/index.php' => [HomeController::class, 'index'],
        '/equipos' => [EquipoController::class, 'index'],
        '/jugadores' => [JugadorController::class, 'index'],
        '/partidos' => [PartidoController::class, 'index'],
        '/partidos/resultado' => [PartidoController::class, 'results'],
        '/estadisticas' => [EstadisticaController::class, 'index'],
    ],
    'POST' => [
        '/login' => [AuthController::class, 'login'],
        '/register' => [AuthController::class, 'register'],
        '/logout' => [AuthController::class, 'logout'],
        '/equipos' => [EquipoController::class, 'save'],
        '/equipos/eliminar' => [EquipoController::class, 'delete'],
        '/jugadores' => [JugadorController::class, 'save'],
        '/jugadores/eliminar' => [JugadorController::class, 'delete'],
        '/partidos' => [PartidoController::class, 'save'],
        '/partidos/resultado' => [PartidoController::class, 'saveResult'],
        '/partidos/eliminar' => [PartidoController::class, 'delete'],
    ],
];
