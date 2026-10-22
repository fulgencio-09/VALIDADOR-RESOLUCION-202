<?php

declare(strict_types=1);

namespace App\Domain\Res202\Correction;

use DateTimeImmutable;
use RuntimeException;

final class RpedCorrectionService
{
    public function apply(string $content, array $corrections): array
    {
        $lines = preg_split('/\r\n|\n|\r/', $content) ?: [];
        $changes = [];

        $control = isset($lines[0]) ? explode('|', $lines[0]) : [];
        $cutoffDate = (($control[0] ?? '') === '1') ? ($control[3] ?? null) : null;

        foreach ($corrections as $correction) {
            $code = (string) ($correction['code'] ?? '');
            $line = (int) ($correction['line'] ?? 0);
            $variable = (int) ($correction['variable'] ?? -1);
            $action = (string) ($correction['action'] ?? '');

            if (RpedCorrectionCatalog::forCode($code) === null) {
                throw new RuntimeException("La corrección {$code} no está habilitada.");
            }
            if ($line < 1 || !isset($lines[$line - 1])) {
                throw new RuntimeException("La línea {$line} no existe en el archivo.");
            }
            if ($variable < 0 || $variable > 118) {
                throw new RuntimeException('La variable RPED debe estar entre 0 y 118.');
            }

            $fields = explode('|', $lines[$line - 1]);

            if ($action === 'omit_error' || $action === 'manual') {
                continue;
            }

            $updates = $this->updatesForAction($action, $fields, $line, $variable, $cutoffDate);

            foreach ($updates as $targetVariable => $newValue) {
                $targetVariable = (int) $targetVariable;

                if (!array_key_exists($targetVariable, $fields)) {
                    throw new RuntimeException("La variable {$targetVariable} no existe en la línea {$line}.");
                }

                $oldValue = $fields[$targetVariable];
                $newValue = (string) $newValue;

                if ($oldValue === $newValue) {
                    continue;
                }

                $fields[$targetVariable] = $newValue;
                $changes[] = [
                    'code' => $code,
                    'line' => $line,
                    'variable' => $targetVariable,
                    'action' => $action,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                ];
            }

            $lines[$line - 1] = implode('|', $fields);
        }

        return ['content' => implode("\r\n", $lines), 'changes' => $changes];
    }

    private function updatesForAction(string $action, array $fields, int $line, int $variable, ?string $cutoffDate = null): array
    {
        return match ($action) {
            'scientific_notation_to_integer' => [
                $variable => $this->scientificToInteger((string) ($fields[$variable] ?? '')),
            ],
            'set_hemoglobin_result_998' => [104 => '998'],
            'set_ldl_date_from_hemoglobin' => [72 => $this->setLdlDateFromHemoglobin($fields, $line)],
            'set_variable_90_from_variable_2' => [90 => $this->setVariable90FromVariable2($fields, $line)],
            'copy_variable_29_to_62' => [62 => $this->copyVariable29($fields, 62, $line)],
            'correct_error_131' => $this->correctError131($fields, $line, $cutoffDate),
            'copy_variable_29_to_63' => [63 => $this->copyVariable29($fields, 63, $line)],
            'copy_variable_29_to_76' => [76 => $this->copyVariable29($fields, 76, $line)],
            'copy_variable_29_to_80' => [80 => $this->copyVariable29($fields, 80, $line)],
            'copy_variable_29_to_82' => [82 => $this->copyVariable29($fields, 82, $line)],
            'copy_variable_29_to_103' => [103 => $this->copyVariable29($fields, 103, $line)],
            'copy_variable_29_to_105' => [105 => $this->copyVariable29($fields, 105, $line)],
            'copy_variable_29_to_96' => [96 => $this->copyVariable29($fields, 96, $line)],
            'set_variable_71_one' => [71 => '1'],
            'set_variable_94_zero' => [94 => '0'],
            'set_variable_14_21' => [14 => '21'],
            'set_variable_16_zero' => [16 => '0'],
            'set_variable_53_1800' => [53 => '1800-01-01'],
            'set_variable_54_21' => [54 => '21'],
            'set_variable_36_zero' => [36 => '0'],
            'set_variable_52_1845' => [52 => '1845-01-01'],
            'set_variable_76_1845' => [76 => '1845-01-01'],
            'set_variable_93_1800' => [93 => '1800-01-01'],
            'set_variable_77_zero' => [77 => '0'],
            'set_variable_77_one' => [77 => '1'],
            'correct_error_328' => $this->correctError328($fields, $line),
            'correct_error_379' => [35 => '21', 49 => '1800-01-01', 50 => '1800-01-01', 56 => '1800-01-01', 58 => '1800-01-01', 59 => '21', 60 => '21', 61 => '21'],
            'set_variable_52_from_29_when_16_4_5' => $this->setVariable52From29When16Is45($fields, $line),
            'correct_error_524' => $this->correctError524($fields, $line),
            'set_variable_27_21_if_29_1800' => $this->setVariableTo21If29Is1800($fields, 27, $line),
            'correct_error_528' => $this->correctError528($fields, $line),
            'set_variable_28_21_if_29_1800' => $this->setVariableTo21If29Is1800($fields, 28, $line),
            'correct_error_550' => $this->correctError550($fields, $line, $cutoffDate),
            'correct_child_development_43_46' => $this->correctChildDevelopment($fields, $line),
            'normalize_variable_102_length' => [102 => $this->normalizeVariable102((string) ($fields[102] ?? ''))],
            'correct_error_639' => $this->correctError639($fields, $line, $cutoffDate),
            'apply_warning_matrix_correction' => $this->applyWarningMatrixCorrection($fields, $line, $variable),
            default => throw new RuntimeException("Acción de corrección no soportada: {$action}."),
        };
    }

    private function scientificToInteger(string $value): string
    {
        if (!preg_match('/^([0-9]+)(?:\.([0-9]+))?[eE]\+?([0-9]+)$/', $value, $matches)) {
            throw new RuntimeException("El valor '{$value}' no es una notación científica decimal soportada.");
        }
        $integer = $matches[1];
        $fraction = $matches[2] ?? '';
        $exponent = (int) $matches[3];
        $digits = $integer . $fraction;
        $decimalPosition = strlen($integer) + $exponent;
        if ($decimalPosition < strlen($digits)) {
            $tail = substr($digits, $decimalPosition);
            if (trim($tail, '0') !== '') {
                throw new RuntimeException("La conversión de '{$value}' produciría decimales y no se aplicó.");
            }
            $digits = substr($digits, 0, $decimalPosition);
        } else {
            $digits .= str_repeat('0', $decimalPosition - strlen($digits));
        }
        return ltrim($digits, '0') ?: '0';
    }

    private function setLdlDateFromHemoglobin(array $fields, int $line): string
    {
        return match ($fields[92] ?? null) {
            '0' => '1845-01-01',
            '998' => '1800-01-01',
            default => throw new RuntimeException("La variable 92 de la línea {$line} debe contener 0 o 998 para corregir automáticamente la variable 72."),
        };
    }

    private function setVariable90FromVariable2(array $fields, int $line): string
    {
        $value89 = $fields[89] ?? null;
        if ($value89 === null) {
            throw new RuntimeException("La variable 89 no existe en la línea {$line}.");
        }
        if ($value89 === '0' || $value89 === '999') {
            throw new RuntimeException("La variable 89 de la línea {$line} debe ser diferente de 0 y 999 para aplicar Error021 automáticamente.");
        }
        if (!array_key_exists(2, $fields)) {
            throw new RuntimeException("La variable 2 no existe en la línea {$line}.");
        }
        return (string) $fields[2];
    }

    private function copyVariable29(array $fields, int $targetVariable, int $line): string
    {
        if (!array_key_exists(29, $fields)) {
            throw new RuntimeException("La variable 29 no existe en la línea {$line}.");
        }
        if (!array_key_exists($targetVariable, $fields)) {
            throw new RuntimeException("La variable {$targetVariable} no existe en la línea {$line}.");
        }
        return (string) $fields[29];
    }

    private function correctError131(array $fields, int $line, ?string $cutoffDate): array
    {
        $date29 = $this->requireVariable($fields, 29, $line);
        if ($date29 !== '1800-01-01' && !$this->isRealDate($date29)) {
            throw new RuntimeException("La variable 29 de la línea {$line} no contiene una fecha válida ni el comodín 1800-01-01 para Error131.");
        }

        if ($cutoffDate === null || !$this->isRealDate($cutoffDate)) {
            throw new RuntimeException("La fecha de corte del registro de control no es válida para calcular la edad de Error131 en la línea {$line}.");
        }

        $birth = $this->requireVariable($fields, 9, $line);
        if (!$this->isRealDate($birth)) {
            throw new RuntimeException("La variable 9 de la línea {$line} no contiene una fecha de nacimiento válida para calcular la edad de Error131.");
        }

        $ageMonths = $this->ageMonthsAtCutoff($birth, $cutoffDate);
        if ($ageMonths < 0) {
            throw new RuntimeException("No fue posible calcular la edad de la línea {$line} para Error131.");
        }

        // El anexo establece la valoración de agudeza visual a partir de los 3 años.
        if ($ageMonths < 36) {
            return [27 => '0', 28 => '0', 62 => '1845-01-01'];
        }

        if ($date29 === '1800-01-01') {
            return [27 => '21', 28 => '21', 62 => '1800-01-01'];
        }

        return [62 => $date29];
    }

    private function correctError328(array $fields, int $line): array
    {
        $updates = [70 => '1'];
        if (in_array($fields[16] ?? null, ['4', '5'], true)) {
            $updates[52] = $this->requireVariable($fields, 29, $line);
        }
        return $updates;
    }

    private function correctError524(array $fields, int $line): array
    {
        $date29 = $this->requireVariable($fields, 29, $line);
        if ($date29 === '1800-01-01') {
            return [27 => '21'];
        }
        if ($this->isRealDate($date29)) {
            return [62 => $date29];
        }
        throw new RuntimeException("La variable 29 de la línea {$line} no contiene una fecha válida ni el comodín 1800-01-01.");
    }

    private function correctError528(array $fields, int $line): array
    {
        $date29 = $this->requireVariable($fields, 29, $line);
        if ($date29 === '1800-01-01') {
            return [28 => '21'];
        }
        if ($this->isRealDate($date29)) {
            return [62 => $date29];
        }
        throw new RuntimeException("La variable 29 de la línea {$line} no contiene una fecha válida ni el comodín 1800-01-01.");
    }

    private function setVariableTo21If29Is1800(array $fields, int $targetVariable, int $line): array
    {
        $date29 = $this->requireVariable($fields, 29, $line);
        return $date29 === '1800-01-01' ? [$targetVariable => '21'] : [];
    }

    private function setVariable52From29When16Is45(array $fields, int $line): array
    {
        if (!in_array($fields[16] ?? null, ['4', '5'], true)) {
            return [];
        }
        return [52 => $this->requireVariable($fields, 29, $line)];
    }

    private function correctError550(array $fields, int $line, ?string $cutoffDate): array
    {
        if ($cutoffDate === null || !$this->isRealDate($cutoffDate)) {
            throw new RuntimeException("La fecha de corte del registro de control no es válida para calcular la edad de Error550 en la línea {$line}.");
        }
        $birth = $this->requireVariable($fields, 9, $line);
        if (!$this->isRealDate($birth)) {
            throw new RuntimeException("La variable 9 de la línea {$line} no contiene una fecha de nacimiento válida para calcular la edad de Error550.");
        }
        $ageMonths = $this->ageMonthsAtCutoff($birth, $cutoffDate);
        if ($ageMonths < 0) {
            throw new RuntimeException("No fue posible calcular la edad de la línea {$line} para Error550.");
        }
        if ($ageMonths < 156) {
            return [40 => '21', 63 => '1800-01-01'];
        }
        return [40 => '0', 63 => '1845-01-01'];
    }

    private function correctChildDevelopment(array $fields, int $line): array
    {
        $cutoff = $this->requireVariable($fields, 29, $line);
        if ($cutoff === '1800-01-01') {
            return [43 => '21', 44 => '21', 45 => '21', 46 => '21', 52 => '1800-01-01'];
        }
        if (!$this->isRealDate($cutoff)) {
            throw new RuntimeException("La variable 29 de la línea {$line} no contiene una fecha válida para Error560/564/568/572.");
        }
        $birth = $this->requireVariable($fields, 9, $line);
        $ageMonths = $this->ageMonthsAtCutoff($birth, $cutoff);
        if ($ageMonths < 0) {
            throw new RuntimeException("La variable 9 de la línea {$line} no contiene una fecha válida para calcular la edad.");
        }
        if ($ageMonths > 84) {
            throw new RuntimeException("La edad de la línea {$line} supera los 7 años establecidos para esta corrección.");
        }
        return [43 => '5', 44 => '5', 45 => '5', 46 => '5', 52 => $cutoff];
    }

    private function normalizeVariable102(string $value): string
    {
        if ($value === '0') {
            return str_repeat('0', 12);
        }
        if (preg_match('/^\d{11}$/', $value)) {
            return '0' . $value;
        }
        if (preg_match('/^\d{12}$/', $value)) {
            return $value;
        }
        return $value;
    }

    private function correctError639(array $fields, int $line, ?string $cutoffDate): array
    {
        if ($cutoffDate === null || !$this->isRealDate($cutoffDate)) {
            throw new RuntimeException("La fecha de corte del registro de control no es válida para calcular la edad de Error639 en la línea {$line}.");
        }
        $birth = $this->requireVariable($fields, 9, $line);
        if (!$this->isRealDate($birth)) {
            throw new RuntimeException("La variable 9 de la línea {$line} no contiene una fecha de nacimiento válida para calcular la edad de Error639.");
        }
        $ageMonths = $this->ageMonthsAtCutoff($birth, $cutoffDate);
        if ($ageMonths < 0) {
            throw new RuntimeException("No fue posible calcular la edad de la línea {$line} para Error639.");
        }
        if ($ageMonths >= 120 && $ageMonths < 216) {
            return [103 => '1800-01-01', 104 => '998'];
        }
        return [103 => '1845-01-01', 104 => '0'];
    }

    private function applyWarningMatrixCorrection(array $fields, int $line, int $variable): array
    {
        return match ($line) {
            679, 896 => $variable === 31 ? [31 => '4'] : [],
            682, 897 => $variable === 31 ? [31 => '21'] : [],
            898 => $variable === 29 && in_array($fields[16] ?? null, ['4', '5'], true)
                ? [52 => $this->requireVariable($fields, 29, $line)]
                : [],
            default => [],
        };
    }

    private function requireVariable(array $fields, int $variable, int $line): string
    {
        if (!array_key_exists($variable, $fields)) {
            throw new RuntimeException("La variable {$variable} no existe en la línea {$line}.");
        }
        return (string) $fields[$variable];
    }

    private function ageMonthsAtCutoff(string $birthValue, string $cutoffValue): int
    {
        if (!$this->isRealDate($birthValue) || !$this->isRealDate($cutoffValue)) {
            return -1;
        }
        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $birthValue);
        $cutoff = DateTimeImmutable::createFromFormat('!Y-m-d', $cutoffValue);
        if ($birth === false || $cutoff === false || $birth > $cutoff) {
            return -1;
        }
        $diff = $birth->diff($cutoff);
        return ($diff->y * 12) + $diff->m;
    }

    private function isRealDate(string $value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value && $value > '1900-01-01';
    }
}
