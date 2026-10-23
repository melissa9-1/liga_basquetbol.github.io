<header class="topbar">
    <a class="brand-mark admin-brand" href="<?= h(url('/')) ?>" aria-label="Liga Basket, inicio">
        <span class="brand-mark__ball" aria-hidden="true">●</span>
        <span class="brand-mark__name">Liga<span>Basket</span></span>
    </a>
    <div class="topbar-heading">
        <p class="eyebrow"><?= h($eyebrow ?? 'Gestión de la liga') ?></p>
        <h1><?= h($title) ?></h1>
    </div>
    <nav class="topbar-actions" aria-label="Navegación administrativa">
        <a class="nav-link" href="<?= h(url('/')) ?>">Inicio</a>
        <a class="nav-link" href="<?= h(url('/equipos/index.php')) ?>">Equipos</a>
        <a class="nav-link" href="<?= h(url('/jugadores/index.php')) ?>">Jugadores</a>
        <a class="nav-link" href="<?= h(url('/partidos/index.php')) ?>">Partidos</a>
        <a class="nav-link" href="<?= h(url('/estadisticas/posiciones.php')) ?>">Estadísticas</a>
        <form method="post" action="<?= h(url('/auth/logout.php')) ?>" class="inline-form">
            <?= csrf_field() ?>
            <button class="button secondary logout-button" type="submit">Salir</button>
        </form>
    </nav>
</header>
