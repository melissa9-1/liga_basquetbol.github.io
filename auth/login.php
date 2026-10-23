<?php

declare(strict_types=1);

require dirname(__DIR__) . '/includes/dispatch.php';

if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
    redirect('/');
}

dispatch_module('/login');
