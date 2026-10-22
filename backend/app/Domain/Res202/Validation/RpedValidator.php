<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

use DateTimeImmutable;

final class RpedValidator
{
    public function __construct(
        private readonly StructuralValidator $structuralValidator,
        private readonly RuleEngine $ruleEngine,
        private readonly RpedCopConsistencyValidator $copConsistencyValidator,
    ) {}

    /** @param array<int,array{name:string,length:int,type:string}> $variables
     *  @param array<int,array<string,mixed>> $rules */
    public function validate(string $content, array $variables, array $rules): array
    {
        $structural = $this->structuralValidator->validate($content, $variables);
        $lines = preg_split('/\r\n|\n|\r/', $content) ?: [];
        $lines = array_values(array_filter($lines, static fn(string $line): bool => $line !== ''));
        $business = [];
        $records = 0;
        $control = isset($lines[0]) ? explode('|', $lines[0]) : [];
        $cutoffDate = (($control[0] ?? '') === '1') ? ($control[3] ?? null) : null;
        $rules = array_merge(
            $rules,
            RpedAdditionalRuleCatalog::executable(),
            RpedBatch294RuleCatalog::executable(),
            RpedBatch535RuleCatalog::executable(),
            RpedBatch600RuleCatalog::executable(),
            RpedBatch638RuleCatalog::executable(),
            RpedBatchCoreSourceRuleCatalog::executable(),
            RpedBatchConsistencySourceRuleCatalog::executable(),
            RpedClarifiedRuleCatalog::executable(),
            RpedEarlierClarifiedRuleCatalog::executable(),
            RpedExternalConsistencyRuleCatalog::executable()
        );
        foreach ($lines as $lineNumber => $line) {
            $fields = explode('|', $line);
            if (($fields[0] ?? '') !== '2' || count($fields) !== count($variables)) {
                continue;
            }
            $record = [];
            foreach ($fields as $index => $value) {
                $record[$index] = $value;
            }
            $records++;
            $line = $lineNumber + 1;
            $ageMonths = $this->ageMonthsAtCutoff($record[9] ?? null, $cutoffDate);
            $ageYears = $this->ageYearsAtCutoff($record[9] ?? null, $cutoffDate);
            $context = $cutoffDate !== null ? ['cutoff_date' => $cutoffDate] : [];
            foreach ($this->ruleEngine->validate($record, $rules, $context) as $result) {
                // Todos los errores asociados a la variable 102 se omiten.
                // La variable 102 no debe generar hallazgos ni correcciones automáticas.
                if ((int) ($result['variable'] ?? -1) === 102) {
                    continue;
                }

                // Error305 solo aplica cuando la edad está entre 10 y 59 años.
                // Menor de 10 o mayor/igual a 60 no debe generar Error305.
                if (($result['code'] ?? '') === 'Error305' && $ageMonths >= 0 && ($ageMonths < 120 || $ageMonths >= 720)) {
                    continue;
                }
                $result['line'] = $line;
                $result['age'] = $ageYears;
                $business[] = $result;
            }
            foreach ($this->copConsistencyValidator->validate($record, $line, $cutoffDate) as $result) {
                if ((int) ($result['variable'] ?? -1) === 102) {
                    continue;
                }
                $result['age'] = $ageYears;
                $business[] = $result;
            }
        }

        // También se omiten los hallazgos estructurales que estén asociados a la variable 102.
        $structural = array_values(array_filter(
            $structural,
            static fn(array $result): bool => (int) ($result['variable'] ?? -1) !== 102
        ));

        $results = array_merge($structural, $business);
        return [
            'valid' => $results === [],
            'records' => $records,
            'errors' => count(array_filter($results, static fn(array $r): bool => ($r['severity'] ?? 'ERROR') !== 'WARNING')),
            'warnings' => count(array_filter($results, static fn(array $r): bool => ($r['severity'] ?? '') === 'WARNING')),
            'results' => $results,
        ];
    }

    private function ageMonthsAtCutoff(mixed $birthValue, mixed $cutoffValue): int
    {
        if (!is_string($birthValue) || !is_string($cutoffValue) || $birthValue === '' || $cutoffValue === '') {
            return -1;
        }
        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $birthValue);
        $cutoff = DateTimeImmutable::createFromFormat('!Y-m-d', $cutoffValue);
        if ($birth === false || $cutoff === false || $birth > $cutoff || $birth < new DateTimeImmutable('1900-01-01')) {
            return -1;
        }
        $diff = $birth->diff($cutoff);
        return ($diff->y * 12) + $diff->m;
    }

    private function ageYearsAtCutoff(mixed $birthValue, mixed $cutoffValue): ?int
    {
        if (!is_string($birthValue) || !is_string($cutoffValue) || $birthValue === '' || $cutoffValue === '') {
            return null;
        }
        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $birthValue);
        $cutoff = DateTimeImmutable::createFromFormat('!Y-m-d', $cutoffValue);
        if ($birth === false || $cutoff === false || $birth > $cutoff || $birth < new DateTimeImmutable('1900-01-01')) {
            return null;
        }
        return $birth->diff($cutoff)->y;
    }
}
