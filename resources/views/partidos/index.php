<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partidos · Liga de Básquetbol</title>
    <link rel="stylesheet" href="<?= h(asset_url('/assets/style.css')) ?>">
</head>
<body>
<div class="container">
    <?php
    $title = 'Partidos';
    $eyebrow = 'Gestión de la liga';
    require dirname(__DIR__, 3) . '/includes/header.php';
    require __DIR__ . '/../partials/messages.php';
    ?>
    <section class="panel">
        <h2>Registrar partido</h2>
        <?php if (count($teams) < 2): ?><p class="empty">Registra al menos dos equipos para programar un partido.</p><?php endif; ?>
        <form method="post" action="<?= h(url('/partidos/form.php')) ?>" class="form-grid">
            <?= csrf_field() ?>
            <label>Equipo local
                <select name="local_team_id" required>
                    <option value="">Selecciona</option>
                    <?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>"><?= h($team['name']) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label>Equipo visitante
                <select name="visitor_team_id" required>
                    <option value="">Selecciona</option>
                    <?php foreach ($teams as $team): ?><option value="<?= (int) $team['id'] ?>"><?= h($team['name']) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label>Fecha<input type="date" name="played_on" required></label>
            <button type="submit" class="button primary" <?= count($teams) < 2 ? 'disabled' : '' ?>>Programar partido</button>
        </form>
    </section>
    <section class="panel">
        <h2>Registrar marcador</h2>
        <p>Cuando termine un partido, registra su resultado desde el módulo de resultados.</p>
        <a class="button primary" href="<?= h(url('/partidos/resultado.php')) ?>">Ir a resultados</a>
    </section>
    <section class="panel">
        <h2>Partidos programados</h2>
        <?php if (!$matches): ?>
            <p class="empty">No hay partidos registrados.</p>
        <?php else: ?>
            <div class="list">
                <?php foreach ($matches as $match): ?>
                    <article class="list-item">
                        <div><strong><?= h($match['local_name']) ?> <?= (int) $match['local_score'] ?> - <?= (int) $match['visitor_score'] ?> <?= h($match['visitor_name']) ?></strong><small><?= h($match['played_on']) ?> · <?= h($match['local_city']) ?> vs <?= h($match['visitor_city']) ?> · <?= (int) $match['played'] === 1 ? 'Finalizado' : 'Pendiente' ?></small></div>
                        <form method="post" action="<?= h(url('/partidos/eliminar.php')) ?>" class="inline-form" onsubmit="return confirm('¿Eliminar este partido?')">
                            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $match['id'] ?>">
                            <button class="button danger" type="submit">Eliminar</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require dirname(__DIR__, 3) . '/includes/footer.php'; ?>
