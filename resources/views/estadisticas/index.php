<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas · Liga de Básquetbol</title>
    <link rel="stylesheet" href="<?= h(url('/assets/css/style.css')) ?>">
</head>
<body>
<div class="container">
    <?php
    $title = 'Estadísticas';
    $eyebrow = 'Reporte de la liga';
    require __DIR__ . '/../partials/admin-header.php';
    require __DIR__ . '/../partials/messages.php';
    ?>
    <div class="stats-layout">
        <section class="panel">
            <h2>Clasificación</h2>
            <?php if (!$standings): ?>
                <p class="empty">No hay estadísticas disponibles.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Equipo</th><th>J</th><th>G</th><th>P</th><th>PF</th><th>PC</th><th>Dif.</th></tr></thead>
                        <tbody>
                            <?php foreach ($standings as $team): ?>
                                <tr>
                                    <td><?= h($team['name']) ?></td><td><?= (int) $team['games'] ?></td>
                                    <td><?= (int) $team['wins'] ?></td><td><?= (int) $team['losses'] ?></td>
                                    <td><?= (int) $team['points_for'] ?></td><td><?= (int) $team['points_against'] ?></td>
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
                        <article class="list-item compact"><div><strong><?= h($match['local_name']) ?> <?= (int) $match['local_score'] ?> - <?= (int) $match['visitor_score'] ?> <?= h($match['visitor_name']) ?></strong><small><?= h($match['played_on']) ?></small></div></article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
</body>
</html>
