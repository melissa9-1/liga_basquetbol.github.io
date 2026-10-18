<?php
require_once __DIR__ . '/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $localTeamId = (int) ($_POST['local_team_id'] ?? 0);
        $visitorTeamId = (int) ($_POST['visitor_team_id'] ?? 0);
        $localScore = (int) ($_POST['local_score'] ?? 0);
        $visitorScore = (int) ($_POST['visitor_score'] ?? 0);
        $playedOn = trim((string) ($_POST['played_on'] ?? ''));

        if ($localTeamId <= 0 || $visitorTeamId <= 0 || $localTeamId === $visitorTeamId || $playedOn === '') {
            flash('error', 'Selecciona dos equipos distintos y define una fecha válida.');
            redirect('partidos.php');
        }

        $stmt = $pdo->prepare('INSERT INTO matches (local_team_id, visitor_team_id, local_score, visitor_score, played_on) VALUES (:local_team_id, :visitor_team_id, :local_score, :visitor_score, :played_on)');
        $stmt->execute([
            ':local_team_id' => $localTeamId,
            ':visitor_team_id' => $visitorTeamId,
            ':local_score' => $localScore,
            ':visitor_score' => $visitorScore,
            ':played_on' => $playedOn,
        ]);

        flash('success', 'Partido registrado correctamente.');
        redirect('partidos.php');
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare('DELETE FROM matches WHERE id = :id')->execute([':id' => $id]);
            flash('success', 'Partido eliminado.');
        }
        redirect('partidos.php');
    }
}

$teams = getTeams($pdo);
$matches = getMatches($pdo);
$successMessage = getFlash('success');
$errorMessage = getFlash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partidos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container small">
        <header class="topbar">
            <div>
                <p class="eyebrow">Gestión</p>
                <h1>Partidos</h1>
            </div>
            <div class="topbar-actions">
                <a class="button secondary" href="index.php">Inicio</a>
                <a class="button secondary" href="logout.php">Salir</a>
            </div>
        </header>

        <?php if ($successMessage): ?><div class="alert success"><?= e($successMessage) ?></div><?php endif; ?>
        <?php if ($errorMessage): ?><div class="alert error"><?= e($errorMessage) ?></div><?php endif; ?>

        <section class="panel">
            <form method="POST" action="partidos.php" class="form-grid">
                <input type="hidden" name="action" value="save">

                <label>
                    Equipo local
                    <select name="local_team_id" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= (int) $team['id'] ?>"><?= e($team['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Equipo visitante
                    <select name="visitor_team_id" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?= (int) $team['id'] ?>"><?= e($team['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Marcador local
                    <input type="number" name="local_score" min="0" value="0" required>
                </label>

                <label>
                    Marcador visitante
                    <input type="number" name="visitor_score" min="0" value="0" required>
                </label>

                <label>
                    Fecha
                    <input type="date" name="played_on" required>
                </label>

                <button type="submit" class="button primary">Guardar partido</button>
            </form>
        </section>

        <section class="panel">
            <h2>Resultados recientes</h2>
            <?php if (!$matches): ?>
                <p class="empty">No hay partidos registrados.</p>
            <?php else: ?>
                <div class="list">
                    <?php foreach ($matches as $match): ?>
                        <div class="list-item">
                            <div>
                                <strong><?= e($match['local_name']) ?> <?= (int) $match['local_score'] ?> - <?= (int) $match['visitor_score'] ?> <?= e($match['visitor_name']) ?></strong>
                                <small><?= e($match['played_on']) ?> · <?= e($match['local_city']) ?> vs <?= e($match['visitor_city']) ?></small>
                            </div>
                            <div class="actions">
                                <form method="POST" action="partidos.php" class="inline-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $match['id'] ?>">
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
