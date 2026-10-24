<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados · Liga de Básquetbol</title>
    <link rel="stylesheet" href="<?= h(asset_url('/assets/style.css')) ?>">
</head>
<body>
<div class="container">
    <?php
    $title = 'Resultados';
    $eyebrow = 'Marcadores de la liga';
    require dirname(__DIR__, 3) . '/includes/header.php';
    require __DIR__ . '/../partials/messages.php';
    ?>
    <section class="panel">
        <h2>Registrar marcadores</h2>
        <?php if (!$matches): ?>
            <p class="empty">No hay partidos programados.</p>
        <?php else: ?>
            <div class="list">
                <?php foreach ($matches as $match): ?>
                    <form method="post" action="<?= h(url('/partidos/resultado.php')) ?>" class="list-item form-grid">
                        <?= csrf_field() ?>
                        <input type="hidden" name="match_id" value="<?= (int) $match['id'] ?>">
                        <div>
                            <strong><?= h($match['local_name']) ?> vs <?= h($match['visitor_name']) ?></strong>
                            <small><?= h($match['played_on']) ?><?= !empty($match['played_at']) ? ' · ' . h(substr($match['played_at'], 0, 5)) : '' ?><?= !empty($match['venue']) ? ' · ' . h($match['venue']) : '' ?> · <?= (int) $match['played'] === 1 ? 'Marcador registrado' : 'Pendiente' ?></small>
                        </div>
                        <label>Marcador local
                            <input type="number" name="local_score" min="0" value="<?= (int) $match['local_score'] ?>" required>
                        </label>
                        <label>Marcador visitante
                            <input type="number" name="visitor_score" min="0" value="<?= (int) $match['visitor_score'] ?>" required>
                        </label>
                        <button type="submit" class="button primary">Guardar marcador</button>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require dirname(__DIR__, 3) . '/includes/footer.php'; ?>
