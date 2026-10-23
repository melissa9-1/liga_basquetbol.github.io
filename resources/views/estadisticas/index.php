<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas · Liga de Básquetbol</title>
    <link rel="stylesheet" href="<?= h(asset_url('/assets/style.css')) ?>">
</head>
<body>
<div class="container">
    <?php
    $title = 'Estadísticas';
    $eyebrow = 'Reporte de la liga';
    require dirname(__DIR__, 3) . '/includes/header.php';
    require __DIR__ . '/../partials/messages.php';
    ?>
    <div class="stats-layout">
        <section class="panel">
            <h2>Clasificación</h2>
            <?php require dirname(__DIR__, 3) . '/includes/tabla_posiciones.php'; ?>
        </section>
        <section class="panel">
            <h2>Últimos partidos</h2>
            <?php if (!$latestMatches): ?>
                <p class="empty">No hay partidos recientes.</p>
            <?php else: ?>
                <div class="list">
                    <?php foreach ($latestMatches as $match): ?>
                        <article class="list-item compact"><div><strong><?= h($match['local_name']) ?> <?= (int) $match['local_score'] ?> - <?= (int) $match['visitor_score'] ?> <?= h($match['visitor_name']) ?></strong><small><?= h($match['played_on']) ?></small></div></article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php require dirname(__DIR__, 3) . '/includes/footer.php'; ?>
