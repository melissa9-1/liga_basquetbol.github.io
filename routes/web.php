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
        '/equipos.php' => [EquipoController::class, 'index'],
        '/jugadores' => [JugadorController::class, 'index'],
        '/jugadores.php' => [JugadorController::class, 'index'],
        '/partidos' => [PartidoController::class, 'index'],
        '/partidos.php' => [PartidoController::class, 'index'],
        '/estadisticas' => [EstadisticaController::class, 'index'],
        '/estadisticas.php' => [EstadisticaController::class, 'index'],
        '/logout.php' => [AuthController::class, 'logout'],
    ],
    'POST' => [
        '/login' => [AuthController::class, 'login'],
        '/register' => [AuthController::class, 'register'],
        '/logout' => [AuthController::class, 'logout'],
        '/equipos' => [EquipoController::class, 'save'],
        '/equipos.php' => [EquipoController::class, 'save'],
        '/equipos/eliminar' => [EquipoController::class, 'delete'],
        '/jugadores' => [JugadorController::class, 'save'],
        '/jugadores.php' => [JugadorController::class, 'save'],
        '/jugadores/eliminar' => [JugadorController::class, 'delete'],
        '/partidos' => [PartidoController::class, 'save'],
        '/partidos.php' => [PartidoController::class, 'save'],
        '/partidos/eliminar' => [PartidoController::class, 'delete'],
    ],
];
