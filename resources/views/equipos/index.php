<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos · Liga de Básquetbol</title>
    <link rel="stylesheet" href="<?= h(asset_url('/assets/style.css')) ?>">
</head>
<body>
<div class="container small">
    <?php
    $title = 'Equipos';
    $eyebrow = 'Gestión de la liga';
    require dirname(__DIR__, 3) . '/includes/header.php';
    require __DIR__ . '/../partials/messages.php';
    ?>
    <section class="panel">
        <h2><?= $editingTeam ? 'Editar equipo' : 'Registrar equipo' ?></h2>
        <form method="post" action="<?= h(url('/equipos/form.php')) ?>" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= h($editingTeam['id'] ?? '') ?>">
            <label>Nombre<input type="text" name="name" maxlength="150" value="<?= h($editingTeam['name'] ?? '') ?>" required></label>
            <label>Ciudad<input type="text" name="city" maxlength="150" value="<?= h($editingTeam['city'] ?? '') ?>" required></label>
            <label>URL del logo<input type="url" name="logo_url" maxlength="2048" placeholder="https://ejemplo.com/logo.png" value="<?= h($editingTeam['logo_url'] ?? '') ?>"></label>
            <label>Entrenador<input type="text" name="coach" maxlength="150" value="<?= h($editingTeam['coach'] ?? '') ?>"></label>
            <label>Año de fundación<input type="number" name="founded_year" min="1800" max="<?= (int) date('Y') ?>" value="<?= h($editingTeam['founded_year'] ?? '') ?>"></label>
            <button type="submit" class="button primary"><?= $editingTeam ? 'Actualizar equipo' : 'Guardar equipo' ?></button>
            <?php if ($editingTeam): ?><a class="button secondary" href="<?= h(url('/equipos/index.php')) ?>">Cancelar</a><?php endif; ?>
        </form>
    </section>
    <section class="panel">
        <h2>Equipos registrados</h2>
        <?php if (!$teams): ?>
            <p class="empty">No hay equipos registrados.</p>
        <?php else: ?>
            <div class="list">
                <?php foreach ($teams as $team): ?>
                    <article class="list-item">
                        <?php if (!empty($team['logo_url'])): ?>
                            <img class="team-logo" src="<?= h($team['logo_url']) ?>" alt="Logo de <?= h($team['name']) ?>" loading="lazy" referrerpolicy="no-referrer">
                        <?php endif; ?>
                        <div class="team-details">
                            <strong><?= h($team['name']) ?></strong>
                            <small><?= h($team['city']) ?></small>
                            <?php if (!empty($team['coach'])): ?><small>Entrenador: <?= h($team['coach']) ?></small><?php endif; ?>
                            <?php if (!empty($team['founded_year'])): ?><small>Fundado en <?= (int) $team['founded_year'] ?></small><?php endif; ?>
                        </div>
                        <div class="actions">
                            <a class="button secondary" href="<?= h(url('/jugadores/index.php?team_id=' . (int) $team['id'])) ?>">Ver jugadores</a>
                            <a class="button secondary" href="<?= h(url('/equipos/form.php?id=' . (int) $team['id'])) ?>">Editar</a>
                            <form method="post" action="<?= h(url('/equipos/eliminar.php')) ?>" class="inline-form" onsubmit="return confirm('¿Eliminar este equipo y sus registros asociados?')">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $team['id'] ?>">
                                <button class="button danger" type="submit">Eliminar</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require dirname(__DIR__, 3) . '/includes/footer.php'; ?>
