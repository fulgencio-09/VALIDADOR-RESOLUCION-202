<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

use DateTimeImmutable;

final class RuleEngine
{
    /**
     * Ejecuta reglas parametrizadas sobre un registro RPED.
     *
     * Las reglas se mantienen fuera del código mediante un catálogo. Esto
     * permite ampliar la cobertura sin convertir el validador en una cadena
     * de condiciones hard-coded.
     */
    public function validate(array $record, array $rules, array $context = []): array
    {
        $results = [];

        foreach ($rules as $rule) {
            if (($rule['active'] ?? true) !== true) {
                continue;
            }

            $operation = (string) ($rule['operation'] ?? '');
            $variable = (int) ($rule['variable'] ?? -1);
            $value = $record[$variable] ?? null;

            $failed = match ($operation) {
                'required' => $value === null || $value === '',
                'in' => !$this->inAllowedValues($value, $rule['values'] ?? []),
                'equals_when' => $this->equalsWhen($record, $rule),
                'equals_when_any' => $this->equalsWhenAny($record, $rule),
                'not_equals_when' => $this->notEqualsWhen($record, $rule),
                'date_not_before' => $this->dateNotBefore($value, $rule['date'] ?? null),
                'date_not_after' => $this->dateNotAfter($value, $rule['date'] ?? null),
                'date_before' => $this->dateBefore($value, $rule['date'] ?? null),
                'length_by_value' => $this->lengthByValue($record, $rule),
                'length_range_by_value' => $this->lengthRangeByValue($record, $rule),
                'length_exact' => $this->lengthExact($value, (int) ($rule['length'] ?? 0)),
                'regex' => $this->regexFails($value, (string) ($rule['pattern'] ?? '')),
                default => false,
            };

            if ($failed) {
                $results[] = [
                    'code' => (string) $rule['code'],
                    'severity' => strtoupper((string) ($rule['severity'] ?? 'ERROR')),
                    'variable' => $variable >= 0 ? $variable : null,
                    'message' => (string) ($rule['message'] ?? 'Regla no cumplida.'),
                    'value' => $value,
                ];
            }
        }

        return $results;
    }

    private function inAllowedValues(mixed $value, array $allowed): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return in_array((string) $value, array_map('strval', $allowed), true);
    }

    private function equalsWhen(array $record, array $rule): bool
    {
        $whenVariable = (int) ($rule['when_variable'] ?? -1);
        $whenValue = (string) ($rule['when_value'] ?? '');
        $expected = (string) ($rule['expected'] ?? '');

        if ((string) ($record[$whenVariable] ?? '') !== $whenValue) {
            return false;
        }

        return (string) ($record[(int) $rule['variable']] ?? '') !== $expected;
    }

    private function equalsWhenAny(array $record, array $rule): bool
    {
        $whenVariable = (int) ($rule['when_variable'] ?? -1);
        $whenValues = array_filter(
            explode('|', (string) ($rule['when_value'] ?? '')),
            static fn (string $v): bool => $v !== ''
        );
        $expected = (string) ($rule['expected'] ?? '');
        $actualCondition = (string) ($record[$whenVariable] ?? '');

        if (!in_array($actualCondition, $whenValues, true)) {
            return false;
        }

        return (string) ($record[(int) $rule['variable']] ?? '') !== $expected;
    }

    private function notEqualsWhen(array $record, array $rule): bool
    {
        $whenVariable = (int) ($rule['when_variable'] ?? -1);
        $whenValue = (string) ($rule['when_value'] ?? '');
        $expected = (string) ($rule['expected'] ?? '');

        if ((string) ($record[$whenVariable] ?? '') === $whenValue) {
            return false;
        }

        return (string) ($record[(int) $rule['variable']] ?? '') === $expected;
    }

    private function dateNotBefore(mixed $value, mixed $date): bool
    {
        if ($value === null || $value === '' || $date === null || $date === '') {
            return false;
        }

        return (string) $value < (string) $date;
    }

    private function dateNotAfter(mixed $value, mixed $date): bool
    {
        if ($value === null || $value === '' || $date === null || $date === '') {
            return false;
        }

        return (string) $value > (string) $date;
    }

    private function dateBefore(mixed $value, mixed $date): bool
    {
        if ($value === null || $value === '' || $date === null || $date === '') {
            return false;
        }

        $actual = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);
        $minimum = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $date);

        if ($actual === false || $minimum === false) {
            return false;
        }

        return $actual < $minimum;
    }

    /**
     * Falla cuando la longitud de la variable objetivo no corresponde al
     * valor de otra variable. Ej.: Error676 para tipo de identificación.
     *
     * rule[length_map] = ["CC" => [["min" => null, "max" => 10]], ...]
     */
    private function lengthByValue(array $record, array $rule): bool
    {
        $selector = (string) ($record[(int) ($rule['selector_variable'] ?? -1)] ?? '');
        $value = (string) ($record[(int) ($rule['variable'] ?? -1)] ?? '');
        $map = $rule['length_map'] ?? [];

        if (!array_key_exists($selector, $map)) {
            return false;
        }

        $length = strlen($value);
        $constraints = $map[$selector];
        foreach ($constraints as $constraint) {
            $min = $constraint['min'] ?? null;
            $max = $constraint['max'] ?? null;
            if (($min === null || $length >= (int) $min) && ($max === null || $length <= (int) $max)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Falla si la longitud no pertenece al conjunto explícito permitido para
     * el valor selector. Se usa para Error678: variable 102 solo 0, 21 o 12.
     */
    private function lengthRangeByValue(array $record, array $rule): bool
    {
        $value = (string) ($record[(int) ($rule['variable'] ?? -1)] ?? '');
        $allowed = array_map('strval', $rule['allowed_values'] ?? []);
        if (in_array($value, $allowed, true)) {
            return false;
        }

        $length = strlen($value);
        $allowedLengths = array_map('intval', $rule['allowed_lengths'] ?? []);
        return !in_array($length, $allowedLengths, true);
    }

    private function lengthExact(mixed $value, int $length): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return strlen((string) $value) !== $length;
    }

    private function regexFails(mixed $value, string $pattern): bool
    {
        if ($value === null || $value === '' || $pattern === '') {
            return false;
        }

        return preg_match($pattern, (string) $value) !== 1;
    }
}
