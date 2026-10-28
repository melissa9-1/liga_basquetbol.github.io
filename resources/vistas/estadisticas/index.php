<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas · Liga de Básquetbol</title>
    <link rel="stylesheet" href="<?= h(asset_url('/assets/style.css')) ?>">
</head>
<body class="<?= is_admin() ? 'admin-body' : 'landing-body' ?>">
<div class="container">
    <?php
    $title = 'Estadísticas';
    $eyebrow = 'Reporte de la liga';
    if (is_admin()) {
        require dirname(__DIR__, 3) . '/includes/header.php';
    } else {
        ?>
        <header class="site-header">
            <div class="nav-shell">
                <a class="brand-mark" href="<?= h(url('/')) ?>" aria-label="Liga Basket, inicio">
                    <span class="brand-mark__ball" aria-hidden="true">●</span>
                    <span class="brand-mark__name">Liga<span>Basket</span></span>
                </a>
                <nav class="main-nav" aria-label="Navegación principal">
                    <a href="<?= h(url('/')) ?>">Inicio</a>
                    <a href="<?= h(url('/#equipos')) ?>">Equipos</a>
                    <a href="<?= h(url('/#resultados')) ?>">Partidos</a>
                    <a href="<?= h(url('/estadisticas/posiciones.php')) ?>" aria-current="page">Estadísticas</a>
                </nav>
            </div>
            <?php if (current_user() !== null): ?>
                <form method="post" action="<?= h(url('/auth/logout.php')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="admin-fab" aria-label="Cerrar sesión">
                        <svg class="admin-fab__icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M10 17l5-5-5-5M15 12H3M12 3h7v18h-7"></path>
                        </svg>
                        <span>Salir</span>
                    </button>
                </form>
            <?php endif; ?>
        </header>
        <?php
    }
    require __DIR__ . '/../fragmentos/mensajes.php';
    ?>
    <div class="stats-layout">
        <section class="panel">
            <h2>Clasificación</h2>
            <?php require dirname(__DIR__, 3) . '/includes/tabla_posiciones.php'; ?>
        </section>
        <section class="panel">
            <h2>Resultados de partidos</h2>
            <?php if (!$completedMatches): ?>
                <p class="empty">No hay resultados registrados.</p>
            <?php else: ?>
                <div class="list">
                    <?php foreach ($completedMatches as $match): ?>
                        <article class="list-item compact">
                            <div>
                                <strong><?= h($match['local_name']) ?> <?= (int) $match['local_score'] ?> - <?= (int) $match['visitor_score'] ?> <?= h($match['visitor_name']) ?></strong>
                                <small><?= h($match['played_on']) ?><?= !empty($match['played_at']) ? ' · ' . h(substr($match['played_at'], 0, 5)) : '' ?><?= !empty($match['venue']) ? ' · ' . h($match['venue']) : '' ?></small>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php require dirname(__DIR__, 3) . '/includes/footer.php'; ?>
