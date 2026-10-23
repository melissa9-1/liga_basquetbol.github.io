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
