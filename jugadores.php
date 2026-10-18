<?php
require_once __DIR__ . '/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $teamId = (int) ($_POST['team_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $position = trim((string) ($_POST['position'] ?? ''));
        $number = (int) ($_POST['number'] ?? 0);

        if ($teamId <= 0 || $name === '' || $position === '' || $number <= 0) {
            flash('error', 'Todos los campos del jugador son obligatorios.');
            redirect('jugadores.php');
        }

        if ($id) {
            $stmt = $pdo->prepare('UPDATE players SET team_id = :team_id, name = :name, position = :position, number = :number WHERE id = :id');
            $stmt->execute([
                ':team_id' => $teamId,
                ':name' => $name,
                ':position' => $position,
                ':number' => $number,
                ':id' => $id,
            ]);
            flash('success', 'Jugador actualizado.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO players (team_id, name, position, number) VALUES (:team_id, :name, :position, :number)');
            $stmt->execute([
                ':team_id' => $teamId,
                ':name' => $name,
                ':position' => $position,
                ':number' => $number,
            ]);
            flash('success', 'Jugador agregado.');
        }

        redirect('jugadores.php');
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM players WHERE id = :id')->execute([':id' => $id]);
            flash('success', 'Jugador eliminado.');
        }
        redirect('jugadores.php');
    }
}

$teams = getTeams($pdo);
$editingPlayer = null;
if (isset($_GET['edit'])) {
    $playerId = (int) $_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM players WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $playerId]);
    $editingPlayer = $stmt->fetch();
}

$players = $pdo->query(
    'SELECT p.*, t.name AS team_name FROM players p JOIN teams t ON t.id = p.team_id ORDER BY t.name ASC, p.number ASC'
)->fetchAll();

$successMessage = getFlash('success');
$errorMessage = getFlash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jugadores</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container small">
        <header class="topbar">
            <div>
                <p class="eyebrow">Gestión</p>
                <h1>Jugadores</h1>
            </div>
            <div class="topbar-actions">
                <a class="button secondary" href="index.php">Inicio</a>
                <a class="button secondary" href="logout.php">Salir</a>
            </div>
        </header>

        <?php if ($successMessage): ?><div class="alert success"><?= e($successMessage) ?></div><?php endif; ?>
        <?php if ($errorMessage): ?><div class="alert error"><?= e($errorMessage) ?></div><?php endif; ?>

        <section class="panel">
            <form method="POST" action="jugadores.php" class="form-grid">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= e($editingPlayer['id'] ?? '') ?>">

                <label>
                    Equipo
                    <select name="team_id" required>
                        <option value="">Seleccione un equipo</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= (int) $team['id'] ?>" <?= (($editingPlayer['team_id'] ?? '') == $team['id']) ? 'selected' : '' ?>>
                                <?= e($team['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Nombre
                    <input type="text" name="name" value="<?= e($editingPlayer['name'] ?? '') ?>" required>
                </label>

                <label>
                    Posición
                    <input type="text" name="position" value="<?= e($editingPlayer['position'] ?? '') ?>" required>
                </label>

                <label>
                    Número
                    <input type="number" name="number" min="1" value="<?= e($editingPlayer['number'] ?? 1) ?>" required>
                </label>

                <button type="submit" class="button primary"><?= $editingPlayer ? 'Actualizar jugador' : 'Guardar jugador' ?></button>
            </form>
        </section>

        <section class="panel">
            <h2>Listado</h2>
            <?php if (!$players): ?>
                <p class="empty">No hay jugadores registrados.</p>
            <?php else: ?>
                <div class="list">
                    <?php foreach ($players as $player): ?>
                        <div class="list-item">
                            <div>
                                <strong><?= e($player['name']) ?></strong>
                                <small><?= e($player['team_name']) ?> · <?= e($player['position']) ?> · #<?= (int) $player['number'] ?></small>
                            </div>
                            <div class="actions">
                                <a class="button secondary" href="jugadores.php?edit=<?= (int) $player['id'] ?>">Editar</a>
                                <form method="POST" action="jugadores.php" class="inline-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $player['id'] ?>">
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
