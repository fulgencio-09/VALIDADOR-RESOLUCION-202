<?php

declare(strict_types=1);

namespace App\Domain\Res202\Correction;

use RuntimeException;

final class RpedCorrectionService
{
    public function apply(string $content, array $corrections): array
    {
        $lines = preg_split('/\r\n|\n|\r/', $content) ?: [];
        $changes = [];

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

            // Estas correcciones cambian dos variables de la misma regla.
            if ($action === 'set_visual_acuity_under_3_left' || $action === 'set_visual_acuity_under_3_right') {
                $firstVariable = $action === 'set_visual_acuity_under_3_left' ? 27 : 28;
                $firstOld = $fields[$firstVariable] ?? null;
                $dateOld = $fields[62] ?? null;

                if ($firstOld === null || $dateOld === null) {
                    throw new RuntimeException("No existen las variables requeridas para {$code} en la línea {$line}.");
                }

                if ($firstOld !== '0') {
                    $fields[$firstVariable] = '0';
                    $changes[] = [
                        'code' => $code,
                        'line' => $line,
                        'variable' => $firstVariable,
                        'action' => $action,
                        'old_value' => $firstOld,
                        'new_value' => '0',
                    ];
                }

                if ($dateOld !== '1845-01-01') {
                    $fields[62] = '1845-01-01';
                    $changes[] = [
                        'code' => $code,
                        'line' => $line,
                        'variable' => 62,
                        'action' => $action,
                        'old_value' => $dateOld,
                        'new_value' => '1845-01-01',
                    ];
                }

                $lines[$line - 1] = implode('|', $fields);
                continue;
            }

            if (!isset($fields[$variable])) {
                throw new RuntimeException("La variable {$variable} no existe en la línea {$line}.");
            }

            $old = $fields[$variable];
            $targetVariable = $variable;

            $new = match ($action) {
                'scientific_notation_to_integer' => $this->scientificToInteger($old),
                'set_hemoglobin_result_998' => $this->setHemoglobinResult998($old),
                'set_ldl_date_from_hemoglobin' => $this->setLdlDateFromHemoglobin($fields, $line),
                'set_variable_90_from_variable_2' => $this->setVariable90FromVariable2($fields, $line),
                'copy_variable_29_to_62' => $this->copyVariable29($fields, 62, $line),
                'copy_variable_29_to_63' => $this->copyVariable29($fields, 63, $line),
                'copy_variable_29_to_76' => $this->copyVariable29($fields, 76, $line),
                'copy_variable_29_to_80' => $this->copyVariable29($fields, 80, $line),
                'copy_variable_29_to_82' => $this->copyVariable29($fields, 82, $line),
                'copy_variable_29_to_103' => $this->copyVariable29($fields, 103, $line),
                'copy_variable_29_to_105' => $this->copyVariable29($fields, 105, $line),
                'copy_variable_29_to_96' => $this->copyVariable29($fields, 96, $line),
                'set_variable_90_zero' => $this->setIfExact($old, '999', '0'),
                'set_variable_93_not_applicable' => $this->setIfExact($old, '1800-01-01', '1845-01-01'),
                'set_variable_94_zero' => $this->setIfExact($old, '999', '0'),
                'set_variable_53_not_applicable' => $this->setIfExact($old, '1800-01-01', '1845-01-01'),
                default => throw new RuntimeException("Acción de corrección no soportada: {$action}."),
            };

            if (in_array($action, [
                'copy_variable_29_to_62', 'copy_variable_29_to_63', 'copy_variable_29_to_76',
                'copy_variable_29_to_80', 'copy_variable_29_to_82', 'copy_variable_29_to_103',
                'copy_variable_29_to_105', 'copy_variable_29_to_96',
            ], true)) {
                $targetVariable = (int) substr($action, strrpos($action, '_') + 1);
                $old = $fields[$targetVariable] ?? '';
            }

            if ($new === $old) {
                continue;
            }

            $fields[$targetVariable] = $new;
            $lines[$line - 1] = implode('|', $fields);
            $changes[] = [
                'code' => $code,
                'line' => $line,
                'variable' => $targetVariable,
                'action' => $action,
                'old_value' => $old,
                'new_value' => $new,
            ];
        }

        return ['content' => implode("\r\n", $lines), 'changes' => $changes];
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

    private function setHemoglobinResult998(string $value): string
    {
        return '998';
    }

    private function setLdlDateFromHemoglobin(array $fields, int $line): string
    {
        $hemoglobin = $fields[92] ?? null;
        if ($hemoglobin === '0') {
            return '1845-01-01';
        }
        if ($hemoglobin === '998') {
            return '1800-01-01';
        }
        throw new RuntimeException("La variable 92 de la línea {$line} debe contener 0 o 998 para corregir automáticamente la fecha LDL.");
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
        return $fields[2];
    }

    private function copyVariable29(array $fields, int $targetVariable, int $line): string
    {
        if (!array_key_exists(29, $fields)) {
            throw new RuntimeException("La variable 29 no existe en la línea {$line}.");
        }
        if (!array_key_exists($targetVariable, $fields)) {
            throw new RuntimeException("La variable {$targetVariable} no existe en la línea {$line}.");
        }
        $source = trim((string) $fields[29]);
        if (!$this->isRealDate($source)) {
            throw new RuntimeException("La variable 29 de la línea {$line} no contiene una fecha válida para usar como corrección.");
        }
        return $source;
    }

    private function setIfExact(string $old, string $expected, string $replacement): string
    {
        return $old === $expected ? $replacement : $old;
    }

    private function isRealDate(string $value): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return false;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value && $value > '1900-01-01';
    }
}
