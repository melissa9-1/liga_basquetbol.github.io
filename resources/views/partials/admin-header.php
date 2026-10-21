<header class="topbar">
    <div>
        <p class="eyebrow"><?= h($eyebrow ?? 'Gestión de la liga') ?></p>
        <h1><?= h($title) ?></h1>
    </div>
    <nav class="topbar-actions" aria-label="Navegación administrativa">
        <a class="button secondary" href="<?= h(url('/')) ?>">Inicio</a>
        <a class="button secondary" href="<?= h(url('/equipos')) ?>">Equipos</a>
        <a class="button secondary" href="<?= h(url('/jugadores')) ?>">Jugadores</a>
        <a class="button secondary" href="<?= h(url('/partidos')) ?>">Partidos</a>
        <a class="button secondary" href="<?= h(url('/estadisticas')) ?>">Estadísticas</a>
        <form method="post" action="<?= h(url('/logout')) ?>" class="inline-form">
            <?= csrf_field() ?>
            <button class="button secondary" type="submit">Cerrar sesión</button>
        </form>
    </nav>
</header>
