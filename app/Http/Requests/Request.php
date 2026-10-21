<?php

declare(strict_types=1);

namespace Liga\Http\Requests;

use Liga\Support\ValidationException;
use PDO;

abstract class Request
{
    public function __construct(protected array $input, protected PDO $pdo)
    {
    }

    protected function requiredText(string $key, string $label, int $maxLength = 255): string
    {
        $rawValue = $this->input[$key] ?? null;
        if (!is_string($rawValue)) {
            throw new ValidationException("{$label} debe ser texto válido.");
        }

        $value = trim($rawValue);
        $length = preg_match_all('/./us', $value);
        if ($value === '' || $length === false || $length > $maxLength) {
            throw new ValidationException("{$label} es obligatorio y no puede superar {$maxLength} caracteres.");
        }
        return $value;
    }

    protected function integer(string $key, string $label, int $minimum = 0): int
    {
        $value = filter_var($this->input[$key] ?? null, FILTER_VALIDATE_INT);
        if ($value === false || $value < $minimum) {
            throw new ValidationException("{$label} debe ser un número igual o mayor que {$minimum}.");
        }
        return $value;
    }
}
