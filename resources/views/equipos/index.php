<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos · Liga de Básquetbol</title>
    <link rel="stylesheet" href="<?= h(url('/assets/css/style.css')) ?>">
</head>
<body>
<div class="container small">
    <?php
    $title = 'Equipos';
    $eyebrow = 'Gestión de la liga';
    require __DIR__ . '/../partials/admin-header.php';
    require __DIR__ . '/../partials/messages.php';
    ?>
    <section class="panel">
        <h2><?= $editingTeam ? 'Editar equipo' : 'Registrar equipo' ?></h2>
        <form method="post" action="<?= h(url('/equipos')) ?>" class="form-grid">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= h($editingTeam['id'] ?? '') ?>">
            <label>Nombre<input type="text" name="name" maxlength="150" value="<?= h($editingTeam['name'] ?? '') ?>" required></label>
            <label>Ciudad<input type="text" name="city" maxlength="150" value="<?= h($editingTeam['city'] ?? '') ?>" required></label>
            <button type="submit" class="button primary"><?= $editingTeam ? 'Actualizar equipo' : 'Guardar equipo' ?></button>
            <?php if ($editingTeam): ?><a class="button secondary" href="<?= h(url('/equipos')) ?>">Cancelar</a><?php endif; ?>
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
                        <div><strong><?= h($team['name']) ?></strong><small><?= h($team['city']) ?></small></div>
                        <div class="actions">
                            <a class="button secondary" href="<?= h(url('/equipos?edit=' . (int) $team['id'])) ?>">Editar</a>
                            <form method="post" action="<?= h(url('/equipos/eliminar')) ?>" class="inline-form" onsubmit="return confirm('¿Eliminar este equipo y sus registros asociados?')">
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
</body>
</html>
