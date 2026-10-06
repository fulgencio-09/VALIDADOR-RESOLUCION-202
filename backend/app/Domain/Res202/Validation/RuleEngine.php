<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

use DateTimeImmutable;

final class RuleEngine
{
    public function validate(array $record, array $rules, array $context = []): array
    {
        $results = [];
        $context = $this->enrichContext($record, $context);

        foreach ($rules as $rule) {
            if (($rule['active'] ?? true) !== true) continue;
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
                'date_before_birth' => $this->dateBeforeBirth($record, $value, $rule),
                'date_after_cutoff' => $this->dateAfterCutoff($value, $context['cutoff_date'] ?? null),
                'date_after_cutoff_plus_days' => $this->dateAfterCutoffPlusDays($value, $context['cutoff_date'] ?? null, (int) ($rule['days'] ?? 0)),
                'date_relation' => $this->dateRelation($record, $variable, $rule),
                'length_by_value' => $this->lengthByValue($record, $rule),
                'length_range_by_value' => $this->lengthRangeByValue($record, $rule),
                'length_exact' => $this->lengthExact($value, (int) ($rule['length'] ?? 0)),
                'regex' => $this->regexFails($value, (string) ($rule['pattern'] ?? '')),
                'forbidden_when' => $this->matchesCondition($record, $rule['when'] ?? [], $context),
                'conditional' => $this->conditionalFails($record, $rule, $context),
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

    private function enrichContext(array $record, array $context): array
    {
        if (!isset($context['cutoff_date']) || !is_string($context['cutoff_date'])) return $context;
        if (isset($context['age_months'], $context['age_years'], $context['age_days'])) return $context;
        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', (string) ($record[9] ?? ''));
        $cutoff = DateTimeImmutable::createFromFormat('!Y-m-d', $context['cutoff_date']);
        if ($birth === false || $cutoff === false || $birth > $cutoff || $birth < new DateTimeImmutable('1900-01-01')) return $context;
        $diff = $birth->diff($cutoff);
        $context['age_years'] = $diff->y;
        $context['age_months'] = ($diff->y * 12) + $diff->m;
        $context['age_days'] = $diff->days ?? 0;
        return $context;
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        if ($value === null || $value === '') return null;
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value);
        return $date === false ? null : $date;
    }

    private function dateAfterCutoff(mixed $value, mixed $cutoff): bool
    {
        $actual = $this->parseDate($value); $limit = $this->parseDate($cutoff);
        return $actual !== null && $limit !== null && $actual > $limit;
    }

    private function dateAfterCutoffPlusDays(mixed $value, mixed $cutoff, int $days): bool
    {
        $actual = $this->parseDate($value); $limit = $this->parseDate($cutoff);
        return $actual !== null && $limit !== null && $actual > $limit->modify(sprintf('+%d days', $days));
    }

    private function dateRelation(array $record, int $variable, array $rule): bool
    {
        $actual = $this->parseDate($record[$variable] ?? null);
        $other = $this->parseDate($record[(int) ($rule['other_variable'] ?? -1)] ?? null);
        if ($actual === null || $other === null) return false;
        return match ((string) ($rule['relation'] ?? 'lt')) {
            'lt' => $actual < $other,
            'lte' => $actual <= $other,
            'gt' => $actual > $other,
            'gte' => $actual >= $other,
            'eq' => $actual == $other,
            'neq' => $actual != $other,
            default => false,
        };
    }

    private function dateBeforeBirth(array $record, mixed $value, array $rule): bool
    {
        if ($value === null || $value === '') return false;
        $wildcards = array_map('strval', $rule['ignore_values'] ?? ['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01']);
        if (in_array((string) $value, $wildcards, true)) return false;
        $birthValue = $record[(int) ($rule['birth_variable'] ?? 9)] ?? null;
        if ($birthValue === null || $birthValue === '' || in_array((string) $birthValue, $wildcards, true)) return false;
        if (isset($rule['min_valid_date']) && $rule['min_valid_date'] !== null && (string) $value <= (string) $rule['min_valid_date']) return false;
        $actual = $this->parseDate($value); $birth = $this->parseDate($birthValue);
        if ($actual === null || $birth === null) return false;
        return (bool) ($rule['inclusive'] ?? true) ? $actual <= $birth : $actual < $birth;
    }

    private function matchesCondition(array $record, array $condition, array $context): bool
    {
        if (isset($condition['all'])) { foreach ($condition['all'] as $child) if (!$this->matchesCondition($record, $child, $context)) return false; return true; }
        if (isset($condition['any'])) { foreach ($condition['any'] as $child) if ($this->matchesCondition($record, $child, $context)) return true; return false; }
        $actual = $this->conditionValue($record, $condition, $context);
        $operator = (string) ($condition['op'] ?? 'eq'); $expected = $condition['value'] ?? null;
        return match ($operator) {
            'eq' => (string) $actual === (string) $expected, 'neq' => (string) $actual !== (string) $expected,
            'in' => in_array((string) $actual, array_map('strval', $condition['values'] ?? []), true),
            'not_in' => !in_array((string) $actual, array_map('strval', $condition['values'] ?? []), true),
            'lt' => $this->compare($actual, $expected) < 0, 'lte' => $this->compare($actual, $expected) <= 0,
            'gt' => $this->compare($actual, $expected) > 0, 'gte' => $this->compare($actual, $expected) >= 0,
            default => false,
        };
    }

    private function conditionValue(array $record, array $condition, array $context): mixed
    {
        if (array_key_exists('field', $condition)) return $record[(int) $condition['field']] ?? null;
        if (array_key_exists('age_years', $condition)) return $context['age_years'] ?? null;
        if (array_key_exists('age_months', $condition)) return $context['age_months'] ?? null;
        if (array_key_exists('age_days', $condition)) return $context['age_days'] ?? null;
        return null;
    }

    private function compare(mixed $actual, mixed $expected): int
    {
        if ($actual === null || $expected === null || $actual === '') return 0;
        if (is_numeric($actual) && is_numeric($expected)) return (float) $actual <=> (float) $expected;
        return (string) $actual <=> (string) $expected;
    }

    private function conditionalFails(array $record, array $rule, array $context): bool
    {
        return $this->matchesCondition($record, $rule['when'] ?? [], $context) && !$this->matchesCondition($record, $rule['require'] ?? [], $context);
    }

    private function inAllowedValues(mixed $value, array $allowed): bool
    {
        if ($value === null || $value === '') return true;
        return in_array((string) $value, array_map('strval', $allowed), true);
    }

    private function equalsWhen(array $record, array $rule): bool
    {
        if ((string) ($record[(int) ($rule['when_variable'] ?? -1)] ?? '') !== (string) ($rule['when_value'] ?? '')) return false;
        return (string) ($record[(int) $rule['variable']] ?? '') !== (string) ($rule['expected'] ?? '');
    }

    private function equalsWhenAny(array $record, array $rule): bool
    {
        $whenValues = array_filter(explode('|', (string) ($rule['when_value'] ?? '')), static fn (string $v): bool => $v !== '');
        if (!in_array((string) ($record[(int) ($rule['when_variable'] ?? -1)] ?? ''), $whenValues, true)) return false;
        return (string) ($record[(int) $rule['variable']] ?? '') !== (string) ($rule['expected'] ?? '');
    }

    private function notEqualsWhen(array $record, array $rule): bool
    {
        if ((string) ($record[(int) ($rule['when_variable'] ?? -1)] ?? '') === (string) ($rule['when_value'] ?? '')) return false;
        return (string) ($record[(int) $rule['variable']] ?? '') === (string) ($rule['expected'] ?? '');
    }

    private function dateNotBefore(mixed $value, mixed $date): bool { return $value !== null && $value !== '' && $date !== null && $date !== '' && (string) $value < (string) $date; }
    private function dateNotAfter(mixed $value, mixed $date): bool { return $value !== null && $value !== '' && $date !== null && $date !== '' && (string) $value > (string) $date; }
    private function dateBefore(mixed $value, mixed $date): bool { $actual=$this->parseDate($value); $minimum=$this->parseDate($date); return $actual !== null && $minimum !== null && $actual < $minimum; }

    private function lengthByValue(array $record, array $rule): bool
    {
        $selector = (string) ($record[(int) ($rule['selector_variable'] ?? -1)] ?? ''); $value = (string) ($record[(int) ($rule['variable'] ?? -1)] ?? '');
        if (!array_key_exists($selector, $rule['length_map'] ?? [])) return false;
        $length = strlen($value); foreach ($rule['length_map'][$selector] as $constraint) { $min=$constraint['min']??null; $max=$constraint['max']??null; if (($min===null||$length>=(int)$min)&&($max===null||$length<=(int)$max)) return false; }
        return true;
    }
    private function lengthRangeByValue(array $record, array $rule): bool
    {
        $value=(string)($record[(int)($rule['variable']??-1)]??''); if(in_array($value,array_map('strval',$rule['allowed_values']??[]),true))return false; if(!in_array(strlen($value),array_map('intval',$rule['allowed_lengths']??[]),true))return true; $pattern=$rule['allowed_pattern']??null; return $pattern!==null&&preg_match((string)$pattern,$value)!==1;
    }
    private function lengthExact(mixed $value,int $length): bool{return $value!==null&&$value!==''&&strlen((string)$value)!==$length;}
    private function regexFails(mixed $value,string $pattern): bool{return $value!==null&&$value!==''&&$pattern!==''&&preg_match($pattern,(string)$value)!==1;}
}