<?php
require_once __DIR__ . '/functions.php';
requireLogin();

$standings = getStandings($pdo);
$matches = getMatches($pdo);
$latestMatches = array_slice($matches, 0, 5);
$successMessage = getFlash('success');
$errorMessage = getFlash('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <p class="eyebrow">Reporte</p>
                <h1>Estadísticas</h1>
            </div>
            <div class="topbar-actions">
                <a class="button secondary" href="index.php">Inicio</a>
                <a class="button secondary" href="logout.php">Salir</a>
            </div>
        </header>

        <?php if ($successMessage): ?><div class="alert success"><?= e($successMessage) ?></div><?php endif; ?>
        <?php if ($errorMessage): ?><div class="alert error"><?= e($errorMessage) ?></div><?php endif; ?>

        <div class="stats-layout">
            <section class="panel">
                <h2>Clasificación</h2>
                <?php if (!$standings): ?>
                    <p class="empty">No hay estadísticas disponibles.</p>
                <?php else: ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Equipo</th>
                                    <th>J</th>
                                    <th>G</th>
                                    <th>P</th>
                                    <th>PF</th>
                                    <th>PC</th>
                                    <th>Dif.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($standings as $team): ?>
                                    <tr>
                                        <td><?= e($team['name']) ?></td>
                                        <td><?= (int) $team['games'] ?></td>
                                        <td><?= (int) $team['wins'] ?></td>
                                        <td><?= (int) $team['losses'] ?></td>
                                        <td><?= (int) $team['points_for'] ?></td>
                                        <td><?= (int) $team['points_against'] ?></td>
                                        <td><?= (int) $team['difference'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="panel">
                <h2>Últimos partidos</h2>
                <?php if (!$latestMatches): ?>
                    <p class="empty">No hay partidos recientes.</p>
                <?php else: ?>
                    <div class="list">
                        <?php foreach ($latestMatches as $match): ?>
                            <div class="list-item compact">
                                <div>
                                    <strong><?= e($match['local_name']) ?> <?= (int) $match['local_score'] ?> - <?= (int) $match['visitor_score'] ?> <?= e($match['visitor_name']) ?></strong>
                                    <small><?= e($match['played_on']) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>
</html>
