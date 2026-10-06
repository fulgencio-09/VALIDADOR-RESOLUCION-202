<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

final class RuleEngine
{
    /**
     * Ejecuta reglas parametrizadas sobre un registro RPED.
     *
     * La regla se expresa como una operación simple y su configuración queda
     * fuera del código del validador. Las reglas complejas se incorporarán
     * progresivamente al mismo contrato.
     *
     * Operaciones soportadas:
     * - required
     * - in
     * - equals_when
     * - not_equals_when
     * - date_not_before
     * - date_not_after
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
                'not_equals_when' => $this->notEqualsWhen($record, $rule),
                'date_not_before' => $this->dateNotBefore($value, $rule['date'] ?? null),
                'date_not_after' => $this->dateNotAfter($value, $rule['date'] ?? null),
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
}
