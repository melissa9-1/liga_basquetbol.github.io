<?php

declare(strict_types=1);

final class EquipoFactory
{
    private static int $sequence = 0;

    public static function make(array $attributes = []): array
    {
        self::$sequence++;
        return array_merge([
            'name' => 'Equipo ' . self::$sequence,
            'city' => 'Ciudad ' . self::$sequence,
        ], $attributes);
    }
}
