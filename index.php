<?php
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'register') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || strlen($password) < 4) {
            flash('error', 'Usuario y contraseña válidos son obligatorios.');
            redirect('index.php');
        }

        try { 
            $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (:username, :password)');
            $stmt->execute([
                ':username' => $username,
                ':password' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            flash('success', 'Usuario registrado correctamente.');
        } catch (Throwable $e) {
            flash('error', 'Ese usuario ya existe.');
            redirect('index.php');
        }

        redirect('index.php');
    }

    if ($action === 'login') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = ['id' => (int) $user['id'], 'username' => $user['username']];
            flash('success', 'Sesión iniciada correctamente.');
            redirect('index.php');
        }

        flash('error', 'Credenciales incorrectas.');
        redirect('index.php');
    }
}

$successMessage = getFlash('success');
$errorMessage = getFlash('error');
$loggedUser = currentUser();
if (!$loggedUser) {
    $publicTeams = getTeams($pdo);
    $allMatches = getMatches($pdo);
    $publicMatches = array_slice($allMatches, 0, 4);
    $allStandings = getStandings($pdo);
    $publicStandings = array_slice($allStandings, 0, 5);
    $publicPlayerCount = (int) $pdo->query('SELECT COUNT(*) FROM players')->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liga de Básquetbol</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="landing-body">
    <?php if ($successMessage): ?>
        <div class="alert success"><?= e($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert error"><?= e($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (!$loggedUser): ?>
        <header class="site-header">
            <div class="nav-shell">
                <a class="brand-mark" href="#inicio" aria-label="Liga de Básquetbol, inicio">
                    <span class="brand-mark__ball" aria-hidden="true">●</span>
                    <span class="brand-mark__name">Liga<span>Basket</span></span>
                </a>
                <nav class="main-nav" aria-label="Navegación principal">
                    <a href="#inicio">Inicio</a>
                    <a href="#equipos">Equipos</a>
                    <a href="#resultados">Partidos</a>
                    <a href="#clasificacion">Clasificación</a>
                </nav>
            </div>

            <button type="button" class="admin-fab" aria-label="Abrir acceso administrativo" aria-controls="admin-panel" aria-expanded="false">
                <svg class="admin-fab__icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="8" r="3.5"></circle>
                    <path d="M5 20c.8-3.3 3.3-5 7-5s6.2 1.7 7 5"></path>
                </svg>
            </button>
        </header>

        <aside class="admin-panel" id="admin-panel" aria-label="Panel administrativo">
            <div class="admin-panel__header">
                <p class="mini-auth__eyebrow">Acceso administrativo</p>
                <button type="button" class="admin-panel__close" aria-label="Cerrar panel">×</button>
            </div>

            <div class="tabs">
                <button class="tab active" type="button" data-target="login-form">Iniciar sesión</button>
                <button class="tab" type="button" data-target="register-form">Registrarse</button>
            </div>

            <form id="login-form" class="auth-form" method="POST" action="index.php">
                <input type="hidden" name="action" value="login">
                <label>
                    Usuario
                    <input type="text" name="username" required>
                </label>
                <label>
                    Contraseña
                    <input type="password" name="password" required>
                </label>
                <button type="submit" class="button primary">Entrar</button>
            </form>

            <form id="register-form" class="auth-form hidden" method="POST" action="index.php">
                <input type="hidden" name="action" value="register">
                <label>
                    Usuario
                    <input type="text" name="username" required>
                </label>
                <label>
                    Contraseña
                    <input type="password" name="password" minlength="4" required>
                </label>
                <button type="submit" class="button primary">Registrarme</button>
            </form>
        </aside>

        <main class="landing-page">
            <section class="hero-section" id="inicio">
                <div class="hero-copy">
                    <p class="hero-kicker">La emoción se vive en la cancha</p>
                    <h1>Liga de<br><span>básquetbol</span></h1>
                    <p class="hero-description">Sigue a tus equipos, consulta los últimos marcadores y descubre cómo se mueve la tabla de posiciones.</p>
                    <div class="hero-actions">
                        <a href="#equipos" class="button action ghost">Conocer equipos</a>
                        <a href="#resultados" class="button action primary">Ver resultados</a>
                    </div>
                </div>

                <div class="hero-visual" aria-label="Diseño de cancha de básquetbol">
                    <div class="court-markings" aria-hidden="true">
                        <span class="court-ball"></span>
                        <span class="court-label">Juega en equipo</span>
                    </div>
                    <a href="#clasificacion" class="booking-badge">Explorar la liga <span aria-hidden="true">→</span></a>
                </div>
            </section>

            <section class="league-highlights" aria-label="Resumen de la liga">
                <article class="highlight-card">
                    <span class="highlight-icon" aria-hidden="true">◉</span>
                    <div><strong><?= count($publicTeams) ?></strong><span>Equipos</span></div>
                </article>
                <article class="highlight-card">
                    <span class="highlight-icon" aria-hidden="true">#</span>
                    <div><strong><?= $publicPlayerCount ?></strong><span>Jugadores</span></div>
                </article>
                <article class="highlight-card">
                    <span class="highlight-icon" aria-hidden="true">VS</span>
                    <div><strong><?= count($allMatches) ?></strong><span>Partidos registrados</span></div>
                </article>
                <article class="highlight-card">
                    <span class="highlight-icon" aria-hidden="true">🏆</span>
                    <div><strong><?= count($allStandings) ?></strong><span>Equipos en tabla</span></div>
                </article>
            </section>

            <section class="league-section" id="equipos">
                <div class="section-heading">
                    <div>
                        <p class="section-kicker">Competencia y comunidad</p>
                        <h2>Equipos de la liga</h2>
                    </div>
                    <span class="section-note">Cada equipo, una ciudad. Un solo juego.</span>
                </div>
                <?php if ($publicTeams): ?>
                    <div class="team-grid">
                        <?php foreach (array_slice($publicTeams, 0, 4) as $team): ?>
                            <article class="team-card">
                                <span class="team-card__emblem" aria-hidden="true">🏀</span>
                                <div>
                                    <h3><?= e($team['name']) ?></h3>
                                    <p><?= e($team['city']) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-state">Aún no hay equipos registrados. La competencia comienza pronto.</p>
                <?php endif; ?>
            </section>

            <section class="league-section league-section--split" id="resultados">
                <div class="results-panel">
                    <div class="section-heading">
                        <div>
                            <p class="section-kicker">La acción continúa</p>
                            <h2>Últimos resultados</h2>
                        </div>
                    </div>
                    <?php if ($publicMatches): ?>
                        <div class="match-list">
                            <?php foreach ($publicMatches as $match): ?>
                                <article class="match-card">
                                    <div class="match-card__teams">
                                        <span><?= e($match['local_name']) ?></span>
                                        <strong><?= (int) $match['local_score'] ?><i>:</i><?= (int) $match['visitor_score'] ?></strong>
                                        <span><?= e($match['visitor_name']) ?></span>
                                    </div>
                                    <time datetime="<?= e($match['played_on']) ?>"><?= e($match['played_on']) ?></time>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="empty-state">Todavía no hay partidos registrados. Vuelve pronto para ver los marcadores.</p>
                    <?php endif; ?>
                </div>

                <div class="standings-panel" id="clasificacion">
                    <div class="section-heading">
                        <div>
                            <p class="section-kicker">Así va la competencia</p>
                            <h2>Clasificación</h2>
                        </div>
                    </div>
                    <?php if ($publicStandings): ?>
                        <div class="standings-list">
                            <?php foreach ($publicStandings as $position => $team): ?>
                                <article class="standing-row">
                                    <span class="standing-rank"><?= $position + 1 ?></span>
                                    <div class="standing-team">
                                        <strong><?= e($team['name']) ?></strong>
                                        <small><?= (int) $team['games'] ?> PJ</small>
                                    </div>
                                    <span class="standing-record"><?= (int) $team['wins'] ?> G - <?= (int) $team['losses'] ?> P</span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="empty-state">La tabla se actualizará cuando se registren equipos y partidos.</p>
                    <?php endif; ?>
                </div>
            </section>

            <footer class="landing-footer">
                <a class="brand-mark" href="#inicio" aria-label="Liga de Básquetbol, volver al inicio">
                    <span class="brand-mark__ball" aria-hidden="true">●</span>
                    <span class="brand-mark__name">Liga<span>Basket</span></span>
                </a>
                <p>La pasión por el básquetbol nos une.</p>
            </footer>
        </main>
    <?php else: ?>
        <div class="container">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Panel administrativo</p>
                    <h1>Liga de Básquetbol</h1>
                </div>
                <div class="topbar-actions">
                    <span class="user-pill"><?= e($loggedUser['username']) ?></span>
                    <a class="button secondary" href="logout.php">Cerrar sesión</a>
                </div>
            </header>

            <main class="dashboard">
                <div class="panel stats-grid">
                    <a class="tile" href="equipos.php">
                        <span class="tile-label">Equipos</span>
                        <strong><?= count(getTeams($pdo)) ?></strong>
                    </a>
                    <a class="tile" href="jugadores.php">
                        <span class="tile-label">Jugadores</span>
                        <strong><?= (int) $pdo->query('SELECT COUNT(*) FROM players')->fetchColumn() ?></strong>
                    </a>
                    <a class="tile" href="partidos.php">
                        <span class="tile-label">Partidos</span>
                        <strong><?= (int) $pdo->query('SELECT COUNT(*) FROM matches')->fetchColumn() ?></strong>
                    </a>
                    <a class="tile" href="estadisticas.php">
                        <span class="tile-label">Estadísticas</span>
                        <strong>Ver</strong>
                    </a>
                </div>

                <div class="panel">
                    <h2>Acciones rápidas</h2>
                    <div class="quick-actions">
                        <a class="button primary" href="equipos.php">Gestionar equipos</a>
                        <a class="button primary" href="jugadores.php">Gestionar jugadores</a>
                        <a class="button primary" href="partidos.php">Registrar partidos</a>
                        <a class="button secondary" href="estadisticas.php">Ver estadísticas</a>
                    </div>
                </div>
            </main>
        </div>
    <?php endif; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>
