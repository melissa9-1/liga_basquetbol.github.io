<aside class="admin-sidebar" aria-label="Panel administrativo">
    <a class="brand-mark admin-sidebar__brand" href="<?= h(url('/')) ?>" aria-label="Liga Basket, inicio">
        <span class="brand-mark__ball" aria-hidden="true">●</span>
        <span class="brand-mark__name">Liga<span>Basket</span></span>
    </a>
    <nav class="admin-sidebar__nav" aria-label="Navegación administrativa">
        <a class="admin-nav-link" href="<?= h(url('/')) ?>" <?= ($title ?? '') === 'Liga de Básquetbol' ? 'aria-current="page"' : '' ?>>
            <span class="admin-nav-link__icon" aria-hidden="true">⌂</span>Inicio
        </a>
        <a class="admin-nav-link" href="<?= h(url('/equipos/index.php')) ?>" <?= ($title ?? '') === 'Equipos' ? 'aria-current="page"' : '' ?>>
            <span class="admin-nav-link__icon" aria-hidden="true">◉</span>Equipos
        </a>
        <a class="admin-nav-link" href="<?= h(url('/jugadores/index.php')) ?>" <?= ($title ?? '') === 'Jugadores' ? 'aria-current="page"' : '' ?>>
            <span class="admin-nav-link__icon" aria-hidden="true">♟</span>Jugadores
        </a>
        <a class="admin-nav-link" href="<?= h(url('/partidos/index.php')) ?>" <?= ($title ?? '') === 'Partidos' ? 'aria-current="page"' : '' ?>>
            <span class="admin-nav-link__icon" aria-hidden="true">VS</span>Partidos
        </a>
        <a class="admin-nav-link" href="<?= h(url('/estadisticas/posiciones.php')) ?>" <?= ($title ?? '') === 'Estadísticas' ? 'aria-current="page"' : '' ?>>
            <span class="admin-nav-link__icon" aria-hidden="true">▥</span>Estadísticas
        </a>
    </nav>
    <form method="post" action="<?= h(url('/auth/logout.php')) ?>" class="admin-sidebar__logout">
        <?= csrf_field() ?>
        <button class="button secondary logout-button" type="submit">Salir del panel</button>
    </form>
</aside>
<header class="topbar">
    <div class="topbar-heading">
        <p class="eyebrow"><?= h($eyebrow ?? 'Gestión de la liga') ?></p>
        <h1><?= h($title) ?></h1>
    </div>
</header>
