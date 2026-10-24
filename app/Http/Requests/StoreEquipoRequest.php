<?php

declare(strict_types=1);

namespace Liga\Http\Requests;

use Liga\Support\ValidationException;

final class StoreEquipoRequest extends Request
{
    public function validated(): array
    {
        $name = $this->requiredText('name', 'El nombre del equipo', 150);
        $city = $this->requiredText('city', 'La ciudad', 150);
        $id = ($this->input['id'] ?? '') === '' ? null : $this->integer('id', 'El equipo', 1);
        $coach = $this->optionalText('coach', 'El entrenador', 150);
        $logoUrl = $this->optionalUrl('logo_url', 'El logo');
        $foundedYear = $this->optionalFoundedYear();

        return compact('id', 'name', 'city', 'logoUrl', 'coach', 'foundedYear');
    }

    private function optionalText(string $key, string $label, int $maxLength): ?string
    {
        $rawValue = $this->input[$key] ?? '';
        if (!is_string($rawValue)) {
            throw new ValidationException("{$label} debe ser texto válido.");
        }

        $value = trim($rawValue);
        $length = preg_match_all('/./us', $value);
        if ($length === false || $length > $maxLength) {
            throw new ValidationException("{$label} no puede superar {$maxLength} caracteres.");
        }

        return $value === '' ? null : $value;
    }

    private function optionalUrl(string $key, string $label): ?string
    {
        $value = $this->optionalText($key, $label, 2048);
        if ($value === null) {
            return null;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);
        if (filter_var($value, FILTER_VALIDATE_URL) === false || !in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
            throw new ValidationException("{$label} debe ser una URL válida que comience con http:// o https://.");
        }

        return $value;
    }

    private function optionalFoundedYear(): ?int
    {
        $value = $this->input['founded_year'] ?? '';
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $year = filter_var($value, FILTER_VALIDATE_INT);
        if ($year === false || $year < 1800 || $year > (int) date('Y')) {
            throw new ValidationException('El año de fundación debe estar entre 1800 y el año actual.');
        }

        return $year;
    }
}
