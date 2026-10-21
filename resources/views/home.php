<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Resultados, equipos y clasificación de la liga de básquetbol.">
    <title>Liga de Básquetbol</title>
    <link rel="stylesheet" href="<?= h(url('/assets/css/style.css')) ?>">
</head>
<body class="<?= $loggedUser ? '' : 'landing-body' ?>">
    <main class="container">
        <?php require __DIR__ . '/partials/messages.php'; ?>

        <?php if ($loggedUser): ?>
            <?php
            $title = 'Liga de Básquetbol';
            $eyebrow = 'Panel administrativo · ' . $loggedUser['username'];
            require __DIR__ . '/partials/admin-header.php';
            ?>
            <section class="dashboard">
                <div class="panel stats-grid">
                    <a class="tile" href="<?= h(url('/equipos')) ?>">
                        <span class="tile-label">Equipos</span>
                        <strong><?= count($teams) ?></strong>
                    </a>
                    <a class="tile" href="<?= h(url('/jugadores')) ?>">
                        <span class="tile-label">Jugadores</span>
                        <strong><?= (int) $playerCount ?></strong>
                    </a>
                    <a class="tile" href="<?= h(url('/partidos')) ?>">
                        <span class="tile-label">Partidos</span>
                        <strong><?= (int) $matchCount ?></strong>
                    </a>
                    <a class="tile" href="<?= h(url('/estadisticas')) ?>">
                        <span class="tile-label">Clasificación</span>
                        <strong>Ver</strong>
                    </a>
                </div>
                <section class="panel">
                    <h2>Acciones rápidas</h2>
                    <div class="quick-actions">
                        <a class="button primary" href="<?= h(url('/equipos')) ?>">Gestionar equipos</a>
                        <a class="button primary" href="<?= h(url('/jugadores')) ?>">Gestionar jugadores</a>
                        <a class="button primary" href="<?= h(url('/partidos')) ?>">Registrar partidos</a>
                        <a class="button secondary" href="<?= h(url('/estadisticas')) ?>">Ver estadísticas</a>
                    </div>
                </section>
            </section>
        <?php else: ?>
            <header class="site-header">
                <div class="nav-shell">
                    <a class="brand-mark" href="#inicio" aria-label="Liga Basket, inicio">
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
                <form id="login-form" class="auth-form" method="post" action="<?= h(url('/login')) ?>">
                    <?= csrf_field() ?>
                    <label>Usuario<input type="text" name="username" maxlength="100" required autocomplete="username"></label>
                    <label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label>
                    <button type="submit" class="button primary">Entrar</button>
                </form>
                <form id="register-form" class="auth-form hidden" method="post" action="<?= h(url('/register')) ?>">
                    <?= csrf_field() ?>
                    <label>Usuario<input type="text" name="username" maxlength="100" required autocomplete="username"></label>
                    <label>Contraseña<input type="password" name="password" minlength="4" required autocomplete="new-password"></label>
                    <button type="submit" class="button primary">Crear cuenta</button>
                </form>
            </aside>

            <div class="landing-page">
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
                    <article class="highlight-card"><span class="highlight-icon" aria-hidden="true">◉</span><div><strong><?= count($teams) ?></strong><span>Equipos</span></div></article>
                    <article class="highlight-card"><span class="highlight-icon" aria-hidden="true">#</span><div><strong><?= (int) $playerCount ?></strong><span>Jugadores</span></div></article>
                    <article class="highlight-card"><span class="highlight-icon" aria-hidden="true">VS</span><div><strong><?= (int) $matchCount ?></strong><span>Partidos registrados</span></div></article>
                    <article class="highlight-card"><span class="highlight-icon" aria-hidden="true">🏆</span><div><strong><?= count($standings) ?></strong><span>Equipos en tabla</span></div></article>
                </section>

                <section class="league-section" id="equipos">
                    <div class="section-heading">
                        <div><p class="section-kicker">Competencia y comunidad</p><h2>Equipos de la liga</h2></div>
                        <span class="section-note">Cada equipo, una ciudad. Un solo juego.</span>
                    </div>
                    <?php if ($teams): ?>
                        <div class="team-grid">
                            <?php foreach (array_slice($teams, 0, 4) as $team): ?>
                                <article class="team-card"><span class="team-card__emblem" aria-hidden="true">🏀</span><div><h3><?= h($team['name']) ?></h3><p><?= h($team['city']) ?></p></div></article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="empty-state">Aún no hay equipos registrados. La competencia comienza pronto.</p>
                    <?php endif; ?>
                </section>

                <section class="league-section league-section--split" id="resultados">
                    <div class="results-panel">
                        <div class="section-heading"><div><p class="section-kicker">La acción continúa</p><h2>Últimos resultados</h2></div></div>
                        <?php if ($matches): ?>
                            <div class="match-list">
                                <?php foreach ($matches as $match): ?>
                                    <article class="match-card">
                                        <div class="match-card__teams"><span><?= h($match['local_name']) ?></span><strong><?= (int) $match['local_score'] ?><i>:</i><?= (int) $match['visitor_score'] ?></strong><span><?= h($match['visitor_name']) ?></span></div>
                                        <time datetime="<?= h($match['played_on']) ?>"><?= h($match['played_on']) ?></time>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="empty-state">Todavía no hay partidos registrados. Vuelve pronto para ver los marcadores.</p>
                        <?php endif; ?>
                    </div>
                    <div class="standings-panel" id="clasificacion">
                        <div class="section-heading"><div><p class="section-kicker">Así va la competencia</p><h2>Clasificación</h2></div></div>
                        <?php if ($standings): ?>
                            <div class="standings-list">
                                <?php foreach (array_slice($standings, 0, 5) as $position => $team): ?>
                                    <article class="standing-row"><span class="standing-rank"><?= $position + 1 ?></span><div class="standing-team"><strong><?= h($team['name']) ?></strong><small><?= (int) $team['games'] ?> PJ</small></div><span class="standing-record"><?= (int) $team['wins'] ?> G - <?= (int) $team['losses'] ?> P</span></article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="empty-state">La tabla se actualizará cuando se registren equipos y partidos.</p>
                        <?php endif; ?>
                    </div>
                </section>
                <footer class="landing-footer">
                    <a class="brand-mark" href="#inicio"><span class="brand-mark__ball" aria-hidden="true">●</span><span class="brand-mark__name">Liga<span>Basket</span></span></a>
                    <p>La pasión por el básquetbol nos une.</p>
                </footer>
            </div>
        <?php endif; ?>
    </main>
    <script src="<?= h(url('/assets/js/script.js')) ?>"></script>
</body>
</html>
