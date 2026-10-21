<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once __DIR__ . '/seeders/DatabaseSeeder.php';

(new DatabaseSeeder())->run($pdo);

echo "Datos de ejemplo cargados si la liga estaba vacía.\n";
