<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

use DateTimeImmutable;

final class RpedCopConsistencyValidator
{
    /** @return array<int,array<string,mixed>> */
    public function validate(array $record, int $line, ?string $cutoffDate): array
    {
        $birth = $this->date($record[9] ?? null);
        $cutoff = $this->date($cutoffDate);
        $activity = (string)($record[76] ?? '');
        $cop = (string)($record[102] ?? '');
        if ($birth === null || $cutoff === null || $this->date($activity) === null || $this->date($activity) <= new DateTimeImmutable('1900-01-01')) {
            return [];
        }
        $age = $birth->diff($cutoff);
        $months = ($age->y * 12) + $age->m;
        if ($months < 6 || $cop === '21' || strlen($cop) !== 12 || !ctype_digit($cop)) {
            return [];
        }
        $components = [];
        for ($i = 0; $i < 12; $i += 2) {
            $components[] = (int)substr($cop, $i, 2);
        }
        $max = $age->y >= 5 ? 32 : 22;
        $totalMax = $age->y >= 5 ? 32 : 20;
        $rangeInvalid = false;
        foreach ($components as $component) {
            if ($component < 0 || $component > $max) {
                $rangeInvalid = true;
                break;
            }
        }
        $sumInvalid = array_sum(array_slice($components, 0, 5)) !== $components[5];
        $totalInvalid = $components[5] > $totalMax;
        if (!$rangeInvalid && !$sumInvalid && !$totalInvalid) {
            return [];
        }
        $code = $age->y >= 5 ? 'Error637' : 'Error636';
        return [[
            'code' => $code,
            'severity' => 'ERROR',
            'variable' => 102,
            'line' => $line,
            'message' => $age->y >= 5
                ? 'El COP debe tener componentes entre 00 y 32 y ser coherente con el total de dientes presentes.'
                : 'El COP debe tener componentes entre 00 y 22 y ser coherente con el total de dientes presentes; para dentición infantil se aplica máximo operativo de 20 dientes.',
            'value' => $cop,
        ]];
    }

    private function date(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') return null;
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', (string)$value);
        return $date === false ? null : $date;
    }
}
