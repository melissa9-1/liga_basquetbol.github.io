<?php
require_once __DIR__ . '/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $name = trim((string) ($_POST['name'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));

        if ($name === '' || $city === '') {
            flash('error', 'Nombre y ciudad son obligatorios.');
            redirect('equipos.php');
        }

        if ($id) {
            $stmt = $pdo->prepare('UPDATE teams SET name = :name, city = :city WHERE id = :id');
            $stmt->execute([':name' => $name, ':city' => $city, ':id' => $id]);
            flash('success', 'Equipo actualizado correctamente.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO teams (name, city) VALUES (:name, :city)');
            $stmt->execute([':name' => $name, ':city' => $city]);
            flash('success', 'Equipo creado correctamente.');
        }

        redirect('equipos.php');
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM teams WHERE id = :id')->execute([':id' => $id]);
            flash('success', 'Equipo eliminado.');
        }
        redirect('equipos.php');
    }
}

$editingTeam = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $editingTeam = $pdo->prepare('SELECT * FROM teams WHERE id = :id LIMIT 1');
    $editingTeam->execute([':id' => $id]);
    $editingTeam = $editingTeam->fetch();
}

$teams = getTeams($pdo);
$successMessage = getFlash('success');
$errorMessage = getFlash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container small">
        <header class="topbar">
            <div>
                <p class="eyebrow">Gestión</p>
                <h1>Equipos</h1>
            </div>
            <div class="topbar-actions">
                <a class="button secondary" href="index.php">Inicio</a>
                <a class="button secondary" href="logout.php">Salir</a>
            </div>
        </header>

        <?php if ($successMessage): ?><div class="alert success"><?= e($successMessage) ?></div><?php endif; ?>
        <?php if ($errorMessage): ?><div class="alert error"><?= e($errorMessage) ?></div><?php endif; ?>

        <section class="panel">
            <form method="POST" action="equipos.php" class="form-grid">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= e($editingTeam['id'] ?? '') ?>">

                <label>
                    Nombre
                    <input type="text" name="name" value="<?= e($editingTeam['name'] ?? '') ?>" required>
                </label>
                <label>
                    Ciudad
                    <input type="text" name="city" value="<?= e($editingTeam['city'] ?? '') ?>" required>
                </label>
                <button type="submit" class="button primary"><?= $editingTeam ? 'Actualizar equipo' : 'Guardar equipo' ?></button>
            </form>
        </section>

        <section class="panel">
            <h2>Listado</h2>
            <?php if (!$teams): ?>
                <p class="empty">No hay equipos registrados.</p>
            <?php else: ?>
                <div class="list">
                    <?php foreach ($teams as $team): ?>
                        <div class="list-item">
                            <div>
                                <strong><?= e($team['name']) ?></strong>
                                <small><?= e($team['city']) ?></small>
                            </div>
                            <div class="actions">
                                <a class="button secondary" href="equipos.php?edit=<?= (int) $team['id'] ?>">Editar</a>
                                <form method="POST" action="equipos.php" class="inline-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $team['id'] ?>">
                                    <button class="button danger" type="submit">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
