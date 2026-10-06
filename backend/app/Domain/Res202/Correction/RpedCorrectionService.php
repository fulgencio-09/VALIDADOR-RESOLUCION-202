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
            if (RpedCorrectionCatalog::forCode($code) === null) throw new RuntimeException("La corrección {$code} no está habilitada.");
            if ($line < 1 || !isset($lines[$line - 1])) throw new RuntimeException("La línea {$line} no existe en el archivo.");
            if ($variable < 0 || $variable > 118) throw new RuntimeException('La variable RPED debe estar entre 0 y 118.');
            $fields = explode('|', $lines[$line - 1]);
            if (!isset($fields[$variable])) throw new RuntimeException("La variable {$variable} no existe en la línea {$line}.");
            $old = $fields[$variable];
            $new = match ($action) {
                'scientific_notation_to_integer' => $this->scientificToInteger($old),
                default => throw new RuntimeException("Acción de corrección no soportada: {$action}."),
            };
            if ($new === $old) throw new RuntimeException("El valor de la línea {$line}, variable {$variable} no requiere esta corrección.");
            $fields[$variable] = $new;
            $lines[$line - 1] = implode('|', $fields);
            $changes[] = ['code'=>$code,'line'=>$line,'variable'=>$variable,'action'=>$action,'old_value'=>$old,'new_value'=>$new];
        }
        return ['content'=>implode("\r\n", $lines), 'changes'=>$changes];
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
            if (trim($tail, '0') !== '') throw new RuntimeException("La conversión de '{$value}' produciría decimales y no se aplicó.");
            $digits = substr($digits, 0, $decimalPosition);
        } else {
            $digits .= str_repeat('0', $decimalPosition - strlen($digits));
        }
        return ltrim($digits, '0') ?: '0';
    }
}
