<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'Liga\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = dirname(__DIR__) . '/app/' . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix))) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec(
    'CREATE TABLE teams (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, city TEXT NOT NULL);
     CREATE TABLE matches (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        local_team_id INTEGER NOT NULL,
        visitor_team_id INTEGER NOT NULL,
        local_score INTEGER NOT NULL,
        visitor_score INTEGER NOT NULL,
        played_on TEXT NOT NULL
     );
     CREATE TABLE players (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        team_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        position TEXT NOT NULL,
        number INTEGER NOT NULL
     );'
);

$teams = new Liga\Models\Equipo($pdo);
$teams->save(null, 'Halcones', 'Monterrey');
$teams->save(null, 'Titanes', 'Guadalajara');
(new Liga\Models\Partido($pdo))->save(1, 2, 88, 80, '2026-10-20');

$standings = (new Liga\Http\Controllers\EstadisticaController($pdo))->standings();
if ($standings[0]['name'] !== 'Halcones' || $standings[0]['wins'] !== 1 || $standings[1]['losses'] !== 1) {
    throw new RuntimeException('El cálculo de clasificación no coincide con el resultado registrado.');
}

$request = new Liga\Http\Requests\StorePartidoRequest([
    'local_team_id' => '1',
    'visitor_team_id' => '2',
    'local_score' => '0',
    'visitor_score' => '0',
    'played_on' => '2026-10-21',
], $pdo);
if ($request->validated()['playedOn'] !== '2026-10-21') {
    throw new RuntimeException('La validación de partido no devolvió los datos esperados.');
}

echo "PHP MVC smoke test passed.\n";
