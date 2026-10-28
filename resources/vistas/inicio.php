<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Resultados, equipos y clasificación de la liga de básquetbol.">
    <title>Liga de Básquetbol</title>
    <link rel="stylesheet" href="<?= h(asset_url('/assets/style.css')) ?>">
</head>
<body class="<?= is_admin() ? 'app-body' : 'landing-body' ?>">
    <main class="container <?= is_admin() ? 'admin-shell' : 'public-shell' ?>">
        <?php require __DIR__ . '/fragmentos/mensajes.php'; ?>

        <?php if (is_admin()): ?>
            <?php
            $title = 'Liga de Básquetbol';
            $eyebrow = 'Panel administrativo · ' . $loggedUser['username'];
            require dirname(__DIR__, 2) . '/includes/header.php';
            ?>
            <section class="dashboard">
                <div class="panel stats-grid">
                    <a class="tile" href="<?= h(url('/equipos/index.php')) ?>">
                        <span class="tile-label">Equipos</span>
                        <strong><?= count($teams) ?></strong>
                    </a>
                    <a class="tile" href="<?= h(url('/jugadores/index.php')) ?>">
                        <span class="tile-label">Jugadores</span>
                        <strong><?= (int) $playerCount ?></strong>
                    </a>
                    <a class="tile" href="<?= h(url('/partidos/index.php')) ?>">
                        <span class="tile-label">Partidos</span>
                        <strong><?= (int) $matchCount ?></strong>
                    </a>
                    <a class="tile" href="<?= h(url('/estadisticas/posiciones.php')) ?>">
                        <span class="tile-label">Clasificación</span>
                        <strong>Ver</strong>
                    </a>
                </div>
                <section class="panel">
                    <h2>Acciones rápidas</h2>
                    <div class="quick-actions">
                        <a class="button primary" href="<?= h(url('/equipos/index.php')) ?>">Gestionar equipos</a>
                        <a class="button primary" href="<?= h(url('/jugadores/index.php')) ?>">Gestionar jugadores</a>
                        <a class="button primary" href="<?= h(url('/partidos/index.php')) ?>">Registrar partidos</a>
                        <a class="button secondary" href="<?= h(url('/estadisticas/posiciones.php')) ?>">Ver estadísticas</a>
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
                        <a href="<?= h(url('/estadisticas/posiciones.php')) ?>">Estadísticas</a>
                    </nav>
                </div>
                <?php if ($loggedUser): ?>
                    <form method="post" action="<?= h(url('/auth/logout.php')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="admin-fab" aria-label="Cerrar sesión">
                            <svg class="admin-fab__icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M10 17l5-5-5-5M15 12H3M12 3h7v18h-7"></path>
                            </svg>
                            <span>Salir</span>
                        </button>
                    </form>
                <?php else: ?>
                <button type="button" class="admin-fab" aria-label="Abrir acceso a la cuenta" aria-controls="admin-panel" aria-expanded="false">
                    <svg class="admin-fab__icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="3.5"></circle>
                        <path d="M5 20c.8-3.3 3.3-5 7-5s6.2 1.7 7 5"></path>
                    </svg>
                    <span>Acceso</span>
                </button>
                <?php endif; ?>
            </header>

            <?php if (!$loggedUser): ?>
            <aside class="admin-panel" id="admin-panel" aria-label="Panel administrativo">
                <div class="admin-panel__header">
                    <p class="mini-auth__eyebrow">Acceso a la cuenta</p>
                    <button type="button" class="admin-panel__close" aria-label="Cerrar panel">×</button>
                </div>
                <div class="tabs">
                    <button class="tab active" type="button" data-target="login-form">Iniciar sesión</button>
                    <button class="tab" type="button" data-target="register-form">Registrarse</button>
                </div>
                <form id="login-form" class="auth-form" method="post" action="<?= h(url('/auth/login.php')) ?>">
                    <?= csrf_field() ?>
                    <label>Usuario<input type="text" name="username" maxlength="100" required autocomplete="username"></label>
                    <label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label>
                    <button type="submit" class="button primary">Entrar</button>
                </form>
                <form id="register-form" class="auth-form hidden" method="post" action="<?= h(url('/auth/registro.php')) ?>">
                    <?= csrf_field() ?>
                    <label>Usuario<input type="text" name="username" maxlength="100" required autocomplete="username"></label>
                    <label>Correo electrónico<input type="email" name="email" maxlength="254" required autocomplete="email"></label>
                    <label>Contraseña<input type="password" name="password" minlength="4" required autocomplete="new-password"></label>
                    <button type="submit" class="button primary">Crear cuenta</button>
                </form>
            </aside>
            <?php endif; ?>

            <div class="landing-page">
                <section class="hero-section" id="inicio">
                    <div class="hero-copy">
                        <div class="hero-copy-content">
                            <a class="hero-wordmark" href="#inicio" aria-label="Liga Basket, inicio">
                                <span class="brand-mark__ball" aria-hidden="true">●</span>
                                <span class="brand-mark__name">Liga<span>Basket</span></span>
                            </a>
                            <p class="hero-kicker">La emoción se vive en la cancha</p>
                            <h1>Liga de <span>básquetbol</span></h1>
                            <p class="hero-description">Sigue a tus equipos, consulta los últimos marcadores y descubre cómo se mueve la tabla de posiciones.</p>
                        </div>
                        <div class="hero-actions">
                            <a href="#equipos" class="button action ghost">Conocer equipos</a>
                            <a href="#resultados" class="button action primary">Ver resultados</a>
                        </div>
                    </div>
                    <div class="hero-visual">
                        <img class="hero-image" src="<?= h(asset_url('img/imagen1.jpg')) ?>" alt="Jugador de básquetbol de los Chicago Bulls encestando">
                        <span class="hero-image-caption">Liga de Básquetbol <i>·</i> Temporada actual</span>
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
                                <article class="team-card">
                                    <?php if (!empty($team['logo_url'])): ?>
                                        <img class="team-card__emblem" src="<?= h($team['logo_url']) ?>" alt="Logo de <?= h($team['name']) ?>" loading="lazy" referrerpolicy="no-referrer">
                                    <?php else: ?>
                                        <span class="team-card__emblem" aria-hidden="true">🏀</span>
                                    <?php endif; ?>
                                    <div>
                                        <h3><?= h($team['name']) ?></h3>
                                        <p><?= h($team['city']) ?></p>
                                        <?php if (!empty($team['coach'])): ?><p>Entrenador: <?= h($team['coach']) ?></p><?php endif; ?>
                                        <?php if (!empty($team['founded_year'])): ?><p>Fundado en <?= (int) $team['founded_year'] ?></p><?php endif; ?>
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
                        <div class="section-heading"><div><p class="section-kicker">La acción continúa</p><h2>Últimos resultados</h2></div></div>
                        <?php if ($matches): ?>
                            <div class="match-list">
                                <?php foreach ($matches as $match): ?>
                                    <article class="match-card">
                                        <div class="match-card__teams"><span><?= h($match['local_name']) ?></span><strong><?= (int) $match['local_score'] ?><i>:</i><?= (int) $match['visitor_score'] ?></strong><span><?= h($match['visitor_name']) ?></span></div>
                                        <time datetime="<?= h($match['played_on'] . (!empty($match['played_at']) ? 'T' . $match['played_at'] : '')) ?>"><?= h($match['played_on']) ?><?= !empty($match['played_at']) ? ' · ' . h(substr($match['played_at'], 0, 5)) : '' ?></time>
                                        <?php if (!empty($match['venue'])): ?><small><?= h($match['venue']) ?></small><?php endif; ?>
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
    <script src="<?= h(asset_url('/assets/js/script.js')) ?>"></script>
    <?php require dirname(__DIR__, 2) . '/includes/footer.php'; ?>
