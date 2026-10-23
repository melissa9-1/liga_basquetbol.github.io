<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jugadores · Liga de Básquetbol</title>
    <link rel="stylesheet" href="<?= h(asset_url('/assets/style.css')) ?>">
</head>
<body>
<div class="container small">
    <?php
    $title = 'Jugadores';
    $eyebrow = 'Gestión de la liga';
    require dirname(__DIR__, 3) . '/includes/header.php';
    require __DIR__ . '/../partials/messages.php';
    ?>
    <section class="panel">
        <h2><?= $editingPlayer ? 'Editar jugador' : 'Registrar jugador' ?></h2>
        <form method="post" action="<?= h(url('/jugadores/form.php')) ?>" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= h($editingPlayer['id'] ?? '') ?>">
            <label>Equipo
                <select name="team_id" required>
                    <option value="">Selecciona un equipo</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?= (int) $team['id'] ?>" <?= (string) ($editingPlayer['team_id'] ?? '') === (string) $team['id'] ? 'selected' : '' ?>><?= h($team['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Nombre<input type="text" name="name" maxlength="150" value="<?= h($editingPlayer['name'] ?? '') ?>" required></label>
            <label>Posición<input type="text" name="position" maxlength="80" value="<?= h($editingPlayer['position'] ?? '') ?>" required></label>
            <label>Número<input type="number" name="number" min="0" value="<?= h($editingPlayer['number'] ?? 0) ?>" required></label>
            <button type="submit" class="button primary"><?= $editingPlayer ? 'Actualizar jugador' : 'Guardar jugador' ?></button>
            <?php if ($editingPlayer): ?><a class="button secondary" href="<?= h(url('/jugadores/index.php')) ?>">Cancelar</a><?php endif; ?>
        </form>
    </section>
    <section class="panel">
        <h2>Jugadores registrados</h2>
        <form method="get" action="<?= h(url('/jugadores/index.php')) ?>" class="form-grid">
            <label>Filtrar por equipo
                <select name="team_id">
                    <option value="">Todos los equipos</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?= (int) $team['id'] ?>" <?= (string) ($teamFilter ?? '') === (string) $team['id'] ? 'selected' : '' ?>><?= h($team['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="button secondary">Filtrar</button>
        </form>
        <?php if (!$players): ?>
            <p class="empty">No hay jugadores registrados.</p>
        <?php else: ?>
            <div class="list">
                <?php foreach ($players as $player): ?>
                    <article class="list-item">
                        <div><strong><?= h($player['name']) ?></strong><small><?= h($player['team_name']) ?> · <?= h($player['position']) ?> · #<?= (int) $player['number'] ?></small></div>
                        <div class="actions">
                            <a class="button secondary" href="<?= h(url('/jugadores/form.php?id=' . (int) $player['id'])) ?>">Editar</a>
                            <form method="post" action="<?= h(url('/jugadores/eliminar.php')) ?>" class="inline-form" onsubmit="return confirm('¿Eliminar este jugador?')">
                                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $player['id'] ?>">
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
