<?php

return static function (PDO $pdo): void {
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $columns = $pdo->query('PRAGMA table_info(users)')->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('email', $columns, true)) {
            $pdo->exec('ALTER TABLE users ADD COLUMN email TEXT');
        }

        $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users(email)');
        return;
    }

    if ($driver === 'mysql') {
        $column = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
        );
        $column->execute(['table' => 'users', 'column' => 'email']);
        if ((int) $column->fetchColumn() === 0) {
            $pdo->exec('ALTER TABLE users ADD COLUMN email VARCHAR(254) NULL');
        }

        $index = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = :table AND index_name = :index'
        );
        $index->execute(['table' => 'users', 'index' => 'users_email_unique']);
        if ((int) $index->fetchColumn() === 0) {
            $pdo->exec('CREATE UNIQUE INDEX users_email_unique ON users(email)');
        }
        return;
    }

    throw new RuntimeException("El controlador PDO {$driver} no está soportado.");
};
