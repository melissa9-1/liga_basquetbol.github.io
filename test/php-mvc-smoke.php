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
$pdo->exec('PRAGMA foreign_keys = ON');
Liga\Support\MigrationRunner::run($pdo, dirname(__DIR__) . '/database/migrations');
Liga\Support\MigrationRunner::run($pdo, dirname(__DIR__) . '/database/migrations');

$teams = new Liga\Models\Equipo($pdo);
$teams->save(null, 'Halcones', 'Monterrey');
$teams->save(null, 'Titanes', 'Guadalajara');
$players = new Liga\Models\Jugador($pdo);
$players->save(null, 1, 'Ana', 'Base', 4);
$players->save(null, 2, 'Luis', 'Alero', 8);
if (count($players->all(1)) !== 1 || $players->all(1)[0]['name'] !== 'Ana') {
    throw new RuntimeException('El filtro de jugadores por equipo no devolvió los registros esperados.');
}

(new Liga\Models\Partido($pdo))->save(1, 2, 88, 80, '2026-10-20');

$standings = (new Liga\Http\Controllers\EstadisticaController($pdo))->standings();
if ($standings[0]['name'] !== 'Halcones' || $standings[0]['wins'] !== 1 || $standings[1]['losses'] !== 1) {
    throw new RuntimeException('El cálculo de clasificación no coincide con el resultado registrado.');
}

(new Liga\Models\Partido($pdo))->schedule(1, 2, '2026-10-21');
$standings = (new Liga\Http\Controllers\EstadisticaController($pdo))->standings();
if ($standings[0]['games'] !== 1) {
    throw new RuntimeException('Un partido pendiente no debe aparecer en la clasificación.');
}

(new Liga\Models\Partido($pdo))->recordResult(2, 70, 75);
$standings = (new Liga\Http\Controllers\EstadisticaController($pdo))->standings();
if ($standings[0]['games'] !== 2) {
    throw new RuntimeException('El marcador registrado no se agregó a la clasificación.');
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

$legacyPdo = new PDO('sqlite::memory:');
$legacyPdo->exec('CREATE TABLE matches (id INTEGER PRIMARY KEY)');
$upgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000003_add_match_played_status.php';
$upgrade($legacyPdo);
$columns = $legacyPdo->query('PRAGMA table_info(matches)')->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('played', $columns, true)) {
    throw new RuntimeException('La migración no agregó el estado a una tabla de partidos existente.');
}

echo "PHP MVC smoke test passed.\n";
