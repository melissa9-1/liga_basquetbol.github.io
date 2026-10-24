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
$users = new Liga\Models\User($pdo);
$registration = (new Liga\Http\Requests\RegisterRequest([
    'username' => 'admin',
    'email' => 'admin@example.com',
    'password' => 'secret',
], $pdo))->validated();
if ($registration['email'] !== 'admin@example.com') {
    throw new RuntimeException('El formulario de registro no validó el correo electrónico.');
}
$users->create($registration['username'], $registration['email'], $registration['password']);
if ($users->findByUsername('admin') === null) {
    throw new RuntimeException('El usuario con correo electrónico no se guardó correctamente.');
}

try {
    $users->create('another-admin', 'admin@example.com', 'secret');
    throw new RuntimeException('Se aceptó un correo electrónico que ya está registrado.');
} catch (PDOException $exception) {
    if ($exception->getCode() !== '23000') {
        throw $exception;
    }
}

try {
    (new Liga\Http\Requests\RegisterRequest([
        'username' => 'invalid-email',
        'email' => 'correo-no-valido',
        'password' => 'secret',
    ], $pdo))->validated();
    throw new RuntimeException('Se aceptó un correo electrónico con formato no válido.');
} catch (Liga\Support\ValidationException) {
}

$teams->save(null, 'Halcones', 'Monterrey');
$teams->save(null, 'Titanes', 'Guadalajara');
$teamRequest = new Liga\Http\Requests\StoreEquipoRequest([
    'name' => 'Lobos',
    'city' => 'Puebla',
    'logo_url' => 'https://example.com/lobos.png',
    'coach' => 'María López',
    'founded_year' => '2001',
], $pdo);
$teamData = $teamRequest->validated();
$teams->save(
    null,
    $teamData['name'],
    $teamData['city'],
    $teamData['logoUrl'],
    $teamData['coach'],
    $teamData['foundedYear']
);
$team = $teams->find(3);
if (
    $team === null
    || $team['logo_url'] !== 'https://example.com/lobos.png'
    || $team['coach'] !== 'María López'
    || (int) $team['founded_year'] !== 2001
) {
    throw new RuntimeException('Los detalles del equipo no se guardaron correctamente.');
}

$teams->save(3, 'Lobos del Sur', 'Puebla', null, null, null);
$team = $teams->find(3);
if ($team === null || $team['logo_url'] !== null || $team['coach'] !== null || $team['founded_year'] !== null) {
    throw new RuntimeException('Los detalles del equipo no se actualizaron o limpiaron correctamente.');
}
$teams->delete(3);

try {
    (new Liga\Http\Requests\StoreEquipoRequest([
        'name' => 'Equipo inválido',
        'city' => 'Ciudad',
        'logo_url' => 'javascript:alert(1)',
    ], $pdo))->validated();
    throw new RuntimeException('Se aceptó una URL insegura para el logo.');
} catch (Liga\Support\ValidationException) {
}

try {
    (new Liga\Http\Requests\StoreEquipoRequest([
        'name' => 'Equipo inválido',
        'city' => 'Ciudad',
        'founded_year' => (string) ((int) date('Y') + 1),
    ], $pdo))->validated();
    throw new RuntimeException('Se aceptó un año de fundación futuro.');
} catch (Liga\Support\ValidationException) {
}

$players = new Liga\Models\Jugador($pdo);
$players->save(null, 1, 'Ana', 'Base', 4);
$players->save(null, 2, 'Luis', 'Alero', 8);
$playerRequest = new Liga\Http\Requests\StoreJugadorRequest([
    'team_id' => '1',
    'name' => 'Sofía',
    'position' => 'Pívot',
    'number' => '12',
    'height' => '182.5',
    'birth_date' => '2002-04-15',
], $pdo);
$playerData = $playerRequest->validated();
$players->save(
    $playerData['id'],
    $playerData['teamId'],
    $playerData['name'],
    $playerData['position'],
    $playerData['number'],
    $playerData['height'],
    $playerData['birthDate']
);
$invalidPlayerInputs = [
    ['height' => '301', 'birth_date' => '2002-04-15'],
    ['height' => '182.5', 'birth_date' => date('Y-m-d', strtotime('+1 day'))],
];
foreach ($invalidPlayerInputs as $details) {
    try {
        (new Liga\Http\Requests\StoreJugadorRequest([
            'team_id' => '1',
            'name' => 'Jugador inválido',
            'position' => 'Base',
            'number' => '10',
            ...$details,
        ], $pdo))->validated();
        throw new RuntimeException('Se aceptaron detalles inválidos para un jugador.');
    } catch (Liga\Support\ValidationException) {
    }
}
$savedPlayer = $players->find(3);
if (
    $savedPlayer === null
    || (float) $savedPlayer['height'] !== 182.5
    || $savedPlayer['birth_date'] !== '2002-04-15'
) {
    throw new RuntimeException('Los detalles del jugador no se guardaron correctamente.');
}
$players->save(3, 1, 'Sofía', 'Alero', 12, null, null);
$savedPlayer = $players->find(3);
if ($savedPlayer === null || $savedPlayer['height'] !== null || $savedPlayer['birth_date'] !== null) {
    throw new RuntimeException('Los detalles del jugador no se actualizaron o limpiaron correctamente.');
}
if (count($players->all(1)) !== 2 || $players->all(1)[0]['name'] !== 'Ana') {
    throw new RuntimeException('El filtro de jugadores por equipo no devolvió los registros esperados.');
}

(new Liga\Models\Partido($pdo))->save(1, 2, 88, 80, '2026-10-20');

$standings = (new Liga\Http\Controllers\EstadisticaController($pdo))->standings();
if ($standings[0]['name'] !== 'Halcones' || $standings[0]['wins'] !== 1 || $standings[1]['losses'] !== 1) {
    throw new RuntimeException('El cálculo de clasificación no coincide con el resultado registrado.');
}

$matches = new Liga\Models\Partido($pdo);
$matches->schedule(1, 2, '2026-10-21', '19:30', 'Gimnasio central');
$scheduledMatch = $matches->all()[0];
if (
    $scheduledMatch['played_at'] !== '19:30'
    || $scheduledMatch['venue'] !== 'Gimnasio central'
    || (int) $scheduledMatch['played'] !== 0
) {
    throw new RuntimeException('Los detalles y estado programado del partido no se guardaron correctamente.');
}
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
    'played_on' => '2026-10-21',
    'played_at' => '20:15',
    'venue' => 'Arena',
    'status' => 'scheduled',
], $pdo);
if ($request->validated()['playedOn'] !== '2026-10-21' || $request->validated()['played'] !== 0) {
    throw new RuntimeException('La validación de partido no devolvió los datos esperados.');
}

try {
    (new Liga\Http\Requests\StorePartidoRequest([
        'local_team_id' => '1',
        'visitor_team_id' => '1',
        'played_on' => '2026-10-21',
        'played_at' => '20:15',
        'venue' => 'Arena',
        'status' => 'scheduled',
    ], $pdo))->validated();
    throw new RuntimeException('Se aceptó un partido entre el mismo equipo.');
} catch (Liga\Support\ValidationException) {
}

$playedRequest = new Liga\Http\Requests\StorePartidoRequest([
    'local_team_id' => '1',
    'visitor_team_id' => '2',
    'played_on' => '2026-10-21',
    'played_at' => '20:15',
    'venue' => 'Arena',
    'status' => 'played',
    'local_score' => '91',
    'visitor_score' => '87',
], $pdo);
$playedData = $playedRequest->validated();
if ($playedData['played'] !== 1 || $playedData['localScore'] !== 91 || $playedData['visitorScore'] !== 87) {
    throw new RuntimeException('La validación del partido jugado no devolvió el estado y marcador esperados.');
}

$legacyPdo = new PDO('sqlite::memory:');
$legacyPdo->exec('CREATE TABLE matches (id INTEGER PRIMARY KEY, played_on TEXT NOT NULL)');
$legacyPdo->exec("INSERT INTO matches (id, played_on) VALUES (1, '2026-10-20')");
$legacyPdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, password TEXT)');
$legacyPdo->exec('CREATE TABLE teams (id INTEGER PRIMARY KEY, name TEXT NOT NULL, city TEXT NOT NULL)');
$legacyPdo->exec('CREATE TABLE players (id INTEGER PRIMARY KEY, team_id INTEGER NOT NULL, name TEXT NOT NULL, position TEXT NOT NULL, number INTEGER NOT NULL)');
$legacyPdo->exec("INSERT INTO teams (id, name, city) VALUES (1, 'Equipo legado', 'Lima')");
$legacyPdo->exec("INSERT INTO players (id, team_id, name, position, number) VALUES (1, 1, 'Jugador legado', 'Base', 7)");
$matchUpgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000007_add_match_details.php';
$matchUpgrade($legacyPdo);
$matchUpgrade($legacyPdo);
$matchColumns = $legacyPdo->query('PRAGMA table_info(matches)')->fetchAll(PDO::FETCH_COLUMN, 1);
$legacyMatch = $legacyPdo->query('SELECT * FROM matches WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
if (
    !in_array('played_at', $matchColumns, true)
    || !in_array('venue', $matchColumns, true)
    || $legacyMatch['played_on'] !== '2026-10-20'
    || $legacyMatch['venue'] !== null
) {
    throw new RuntimeException('La migración de detalles no preservó los partidos existentes.');
}
$emailUpgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000004_add_email_to_users.php';
$emailUpgrade($legacyPdo);
$emailColumns = $legacyPdo->query('PRAGMA table_info(users)')->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('email', $emailColumns, true)) {
    throw new RuntimeException('La migración no agregó correo a una tabla de usuarios existente.');
}
$emailUpgrade($legacyPdo);

$teamUpgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000005_add_team_details.php';
$teamUpgrade($legacyPdo);
$teamUpgrade($legacyPdo);
$teamColumns = $legacyPdo->query('PRAGMA table_info(teams)')->fetchAll(PDO::FETCH_COLUMN, 1);
$legacyTeam = $legacyPdo->query('SELECT * FROM teams WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
if (
    !in_array('logo_url', $teamColumns, true)
    || !in_array('coach', $teamColumns, true)
    || !in_array('founded_year', $teamColumns, true)
    || $legacyTeam['name'] !== 'Equipo legado'
    || $legacyTeam['logo_url'] !== null
) {
    throw new RuntimeException('La migración de detalles no preservó los equipos existentes.');
}

$playerUpgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000006_add_player_details.php';
$playerUpgrade($legacyPdo);
$playerUpgrade($legacyPdo);
$playerColumns = $legacyPdo->query('PRAGMA table_info(players)')->fetchAll(PDO::FETCH_COLUMN, 1);
$legacyPlayer = $legacyPdo->query('SELECT * FROM players WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
if (
    !in_array('height', $playerColumns, true)
    || !in_array('birth_date', $playerColumns, true)
    || $legacyPlayer['name'] !== 'Jugador legado'
    || $legacyPlayer['height'] !== null
) {
    throw new RuntimeException('La migración de detalles no preservó los jugadores existentes.');
}

$upgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000003_add_match_played_status.php';
$upgrade($legacyPdo);
$columns = $legacyPdo->query('PRAGMA table_info(matches)')->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('played', $columns, true)) {
    throw new RuntimeException('La migración no agregó el estado a una tabla de partidos existente.');
}

echo "PHP MVC smoke test passed.\n";
