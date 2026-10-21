<?php

declare(strict_types=1);

namespace Liga\Models;

use PDO;

abstract class Model
{
    public function __construct(protected PDO $pdo)
    {
    }

    protected function fetchAll(string $sql, array $parameters = []): array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }

    protected function fetchOne(string $sql, array $parameters = []): ?array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);
        $result = $statement->fetch();
        return $result === false ? null : $result;
    }

    protected function run(string $sql, array $parameters = []): void
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);
    }
}
