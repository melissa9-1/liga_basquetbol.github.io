<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'Liga\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    if ($relativeClass === 'Models\\User') {
        $relativeClass = 'Models\\Usuario';
    }

    $path = dirname(__DIR__) . '/app/' . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('PRAGMA foreign_keys = ON');
Liga\Support\MigrationRunner::run($pdo, dirname(__DIR__) . '/database/migrations');
Liga\Support\MigrationRunner::run($pdo, dirname(__DIR__) . '/database/migrations');

$tableNames = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(PDO::FETCH_COLUMN);
foreach (['usuarios', 'equipos', 'jugadores', 'partidos'] as $tableName) {
    if (!in_array($tableName, $tableNames, true)) {
        throw new RuntimeException("La migración no creó la tabla {$tableName}.");
    }
}
foreach (['users', 'teams', 'players', 'matches'] as $legacyTableName) {
    if (in_array($legacyTableName, $tableNames, true)) {
        throw new RuntimeException("La tabla anterior {$legacyTableName} no se renombró.");
    }
}
$expectedColumns = [
    'usuarios' => ['nombre_usuario', 'correo', 'contrasena', 'rol'],
    'equipos' => ['nombre', 'ciudad', 'url_logo', 'entrenador', 'anio_fundacion'],
    'jugadores' => ['equipo_id', 'nombre', 'posicion', 'numero', 'estatura', 'fecha_nacimiento'],
    'partidos' => [
        'equipo_local_id', 'equipo_visitante_id', 'puntos_local', 'puntos_visitante',
        'fecha_juego', 'hora_juego', 'sede', 'jugado',
    ],
];
foreach ($expectedColumns as $tableName => $columns) {
    $actualColumns = $pdo->query("PRAGMA table_info(\"{$tableName}\")")->fetchAll(PDO::FETCH_COLUMN, 1);
    foreach ($columns as $columnName) {
        if (!in_array($columnName, $actualColumns, true)) {
            throw new RuntimeException("La tabla {$tableName} no contiene la columna {$columnName}.");
        }
    }
    $correoIndexes = $pdo->query('PRAGMA index_list(usuarios)')->fetchAll(PDO::FETCH_ASSOC);
    $uniqueCorreoIndexes = 0;
    foreach ($correoIndexes as $index) {
        if ((int) $index['unique'] === 1) {
            $indexName = str_replace('"', '""', (string) $index['name']);
            $indexColumns = $pdo->query("PRAGMA index_info(\"{$indexName}\")")->fetchAll(PDO::FETCH_COLUMN, 2);
            if (in_array('correo', $indexColumns, true)) {
                $uniqueCorreoIndexes++;
            }
        }
    }
    if ($uniqueCorreoIndexes !== 1) {
        throw new RuntimeException('La migración debe crear un único índice de correo.');
    }
}

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
$registeredUser = $users->findByUsername('admin');
if ($registeredUser === null) {
    throw new RuntimeException('El usuario con correo electrónico no se guardó correctamente.');
}
if ($registeredUser['role'] !== 'usuario') {
    throw new RuntimeException('Las cuentas registradas deben tener el rol de usuario normal.');
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
$legacyPdo->exec('CREATE TABLE partidos (id INTEGER PRIMARY KEY, fecha_juego TEXT NOT NULL)');
$legacyPdo->exec("INSERT INTO partidos (id, fecha_juego) VALUES (1, '2026-10-20')");
$legacyPdo->exec('CREATE TABLE usuarios (id INTEGER PRIMARY KEY, nombre_usuario TEXT, contrasena TEXT)');
$legacyPdo->exec("INSERT INTO usuarios (id, nombre_usuario, contrasena) VALUES (1, 'cuenta_legada', 'hash')");
$legacyPdo->exec('CREATE TABLE equipos (id INTEGER PRIMARY KEY, nombre TEXT NOT NULL, ciudad TEXT NOT NULL)');
$legacyPdo->exec('CREATE TABLE jugadores (id INTEGER PRIMARY KEY, equipo_id INTEGER NOT NULL, nombre TEXT NOT NULL, posicion TEXT NOT NULL, numero INTEGER NOT NULL)');
$legacyPdo->exec("INSERT INTO equipos (id, nombre, ciudad) VALUES (1, 'Equipo legado', 'Lima')");
$legacyPdo->exec("INSERT INTO jugadores (id, equipo_id, nombre, posicion, numero) VALUES (1, 1, 'Jugador legado', 'Base', 7)");
$matchUpgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000007_add_match_details.php';
$matchUpgrade($legacyPdo);
$matchUpgrade($legacyPdo);
$matchColumns = $legacyPdo->query('PRAGMA table_info(partidos)')->fetchAll(PDO::FETCH_COLUMN, 1);
$legacyMatch = $legacyPdo->query('SELECT * FROM partidos WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
if (
    !in_array('hora_juego', $matchColumns, true)
    || !in_array('sede', $matchColumns, true)
    || $legacyMatch['fecha_juego'] !== '2026-10-20'
    || $legacyMatch['sede'] !== null
) {
    throw new RuntimeException('La migración de detalles no preservó los partidos existentes.');
}
$emailUpgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000004_add_email_to_users.php';
$emailUpgrade($legacyPdo);
$emailColumns = $legacyPdo->query('PRAGMA table_info(usuarios)')->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('correo', $emailColumns, true)) {
    throw new RuntimeException('La migración no agregó correo a una tabla de usuarios existente.');
}
$emailUpgrade($legacyPdo);

$importedSchemaPdo = new PDO('sqlite::memory:');
$importedSchemaPdo->exec('CREATE TABLE usuarios (id INTEGER PRIMARY KEY, nombre_usuario TEXT, correo TEXT UNIQUE, contrasena TEXT, rol TEXT)');
$emailUpgrade($importedSchemaPdo);
$importedIndexes = $importedSchemaPdo->query('PRAGMA index_list(usuarios)')->fetchAll(PDO::FETCH_ASSOC);
$importedUniqueIndexes = array_filter($importedIndexes, static fn (array $index): bool => (int) $index['unique'] === 1);
if (count($importedUniqueIndexes) !== 1) {
    throw new RuntimeException('La migración duplicó el índice de correo de un esquema importado.');
}

$teamUpgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000005_add_team_details.php';
$teamUpgrade($legacyPdo);
$teamUpgrade($legacyPdo);
$teamColumns = $legacyPdo->query('PRAGMA table_info(equipos)')->fetchAll(PDO::FETCH_COLUMN, 1);
$legacyTeam = $legacyPdo->query('SELECT * FROM equipos WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
if (
    !in_array('url_logo', $teamColumns, true)
    || !in_array('entrenador', $teamColumns, true)
    || !in_array('anio_fundacion', $teamColumns, true)
    || $legacyTeam['nombre'] !== 'Equipo legado'
    || $legacyTeam['url_logo'] !== null
) {
    throw new RuntimeException('La migración de detalles no preservó los equipos existentes.');
}

$playerUpgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000006_add_player_details.php';
$playerUpgrade($legacyPdo);
$playerUpgrade($legacyPdo);
$playerColumns = $legacyPdo->query('PRAGMA table_info(jugadores)')->fetchAll(PDO::FETCH_COLUMN, 1);
$legacyPlayer = $legacyPdo->query('SELECT * FROM jugadores WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
if (
    !in_array('estatura', $playerColumns, true)
    || !in_array('fecha_nacimiento', $playerColumns, true)
    || $legacyPlayer['nombre'] !== 'Jugador legado'
    || $legacyPlayer['estatura'] !== null
) {
    throw new RuntimeException('La migración de detalles no preservó los jugadores existentes.');
}

$upgrade = require dirname(__DIR__) . '/database/migrations/2026_10_20_000003_add_match_played_status.php';
$upgrade($legacyPdo);
$columns = $legacyPdo->query('PRAGMA table_info(partidos)')->fetchAll(PDO::FETCH_COLUMN, 1);
if (!in_array('jugado', $columns, true)) {
    throw new RuntimeException('La migración no agregó el estado a una tabla de partidos existente.');
}

$roleUpgrade = require dirname(__DIR__) . '/database/migrations/2026_10_28_000001_add_role_to_users.php';
$roleUpgrade($legacyPdo);
$renameTables = require dirname(__DIR__) . '/database/migrations/2026_10_28_000002_rename_tables_to_spanish.php';
$renameTables($legacyPdo);
$translateColumns = require dirname(__DIR__) . '/database/migrations/2026_10_28_000003_translate_columns_to_spanish.php';
$translateColumns($legacyPdo);
$translatedRole = $legacyPdo->query('SELECT rol FROM usuarios WHERE id = 1')->fetchColumn();
if ($translatedRole !== 'usuario') {
    throw new RuntimeException('La migración no estableció el rol normal en español.');
}

$renamePdo = new PDO('sqlite::memory:');
$renamePdo->exec('PRAGMA foreign_keys = ON');
$renamePdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, username TEXT, email TEXT, password TEXT, role TEXT)');
$renamePdo->exec('CREATE TABLE teams (id INTEGER PRIMARY KEY, name TEXT, city TEXT, logo_url TEXT, coach TEXT, founded_year INTEGER)');
$renamePdo->exec('CREATE TABLE players (id INTEGER PRIMARY KEY, team_id INTEGER, name TEXT, position TEXT, number INTEGER, height REAL, birth_date TEXT, FOREIGN KEY (team_id) REFERENCES teams(id))');
$renamePdo->exec('CREATE TABLE matches (id INTEGER PRIMARY KEY, local_team_id INTEGER, visitor_team_id INTEGER, local_score INTEGER, visitor_score INTEGER, played_on TEXT, played_at TEXT, venue TEXT, played INTEGER, FOREIGN KEY (local_team_id) REFERENCES teams(id), FOREIGN KEY (visitor_team_id) REFERENCES teams(id))');
$renamePdo->exec("INSERT INTO users VALUES (1, 'cuenta_legada', 'cuenta@example.com', 'hash', 'admin')");
$renamePdo->exec("INSERT INTO teams VALUES (1, 'Equipo legado', 'Lima', NULL, NULL, NULL)");
$renamePdo->exec("INSERT INTO teams VALUES (2, 'Equipo visitante', 'Cusco', NULL, NULL, NULL)");
$renamePdo->exec("INSERT INTO players VALUES (1, 1, 'Jugador legado', 'Base', 7, NULL, NULL)");
$renamePdo->exec("INSERT INTO matches VALUES (1, 1, 2, 70, 65, '2026-10-20', NULL, NULL, 1)");
$renameTables = require dirname(__DIR__) . '/database/migrations/2026_10_28_000002_rename_tables_to_spanish.php';
$renameTables($renamePdo);
$translateColumns($renamePdo);
$translateColumns($renamePdo);
$foreignKeyErrors = $renamePdo->query('PRAGMA foreign_key_check')->fetchAll(PDO::FETCH_ASSOC);
$translatedColumns = [
    'usuarios' => ['nombre_usuario', 'correo', 'contrasena', 'rol'],
    'equipos' => ['nombre', 'ciudad', 'url_logo', 'entrenador', 'anio_fundacion'],
    'jugadores' => ['equipo_id', 'nombre', 'posicion', 'numero', 'estatura', 'fecha_nacimiento'],
    'partidos' => [
        'equipo_local_id', 'equipo_visitante_id', 'puntos_local', 'puntos_visitante',
        'fecha_juego', 'hora_juego', 'sede', 'jugado',
    ],
];
foreach ($translatedColumns as $tableName => $columns) {
    $actualColumns = $renamePdo->query("PRAGMA table_info(\"{$tableName}\")")->fetchAll(PDO::FETCH_COLUMN, 1);
    foreach ($columns as $columnName) {
        if (!in_array($columnName, $actualColumns, true)) {
            throw new RuntimeException("La migración no tradujo la columna {$tableName}.{$columnName}.");
        }
    }
}
$translatedRole = $renamePdo->query('SELECT rol FROM usuarios WHERE id = 1')->fetchColumn();
$renamedMatch = $renamePdo->query('SELECT * FROM partidos WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
$renamedTeam = $renamePdo->query('SELECT * FROM equipos WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
$renamedPlayer = $renamePdo->query('SELECT * FROM jugadores WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
$renamedUser = $renamePdo->query('SELECT * FROM usuarios WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
if (
    $renamedMatch['fecha_juego'] !== '2026-10-20'
    || (int) $renamedMatch['puntos_local'] !== 70
    || $renamedTeam['nombre'] !== 'Equipo legado'
    || $renamedPlayer['nombre'] !== 'Jugador legado'
    || $renamedUser['nombre_usuario'] !== 'cuenta_legada'
    || $translatedRole !== 'administrador'
    || $foreignKeyErrors !== []
) {
    throw new RuntimeException('La migración de columnas no conservó o tradujo correctamente los datos.');
}

echo "PHP MVC smoke test passed.\n";
