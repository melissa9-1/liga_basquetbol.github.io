<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/dispatch.php';

if (!isset($_GET['edit']) && isset($_GET['id'])) {
    $_GET['edit'] = $_GET['id'];
}

dispatch_module('/jugadores');
