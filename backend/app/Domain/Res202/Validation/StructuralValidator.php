<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

use DateTimeImmutable;

final class StructuralValidator
{
    /**
     * Valida la estructura física del Anexo Técnico 1 (RPED).
     * Esta capa no ejecuta todavía las reglas de negocio del catálogo.
     *
     * @param array<int,array{name:string,length:int,type:string}> $variables
     * @return array<int,array<string,mixed>>
     */
    public function validate(string $content, array $variables): array
    {
        $errors = [];
        $lines = preg_split('/\r\n|\n|\r/', $content) ?: [];
        $lines = array_values(array_filter($lines, static fn (string $line): bool => $line !== ''));

        if ($lines === []) {
            return [$this->error('STRUCT001', null, null, 'El archivo está vacío.')];
        }

        if (str_starts_with($lines[0], "\xEF\xBB\xBF")) {
            $errors[] = $this->error('STRUCT002', 1, null, 'El archivo contiene BOM UTF-8; debe procesarse sin caracteres de encabezado.');
            $lines[0] = substr($lines[0], 3);
        }

        $control = explode('|', $lines[0]);
        if (($control[0] ?? null) !== '1') {
            $errors[] = $this->error('STRUCT003', 1, 0, 'El primer registro debe ser de tipo 1 (control).');
        }
        if (count($control) !== 5) {
            $errors[] = $this->error('STRUCT004', 1, null, 'El registro tipo 1 debe contener exactamente 5 campos.');
        }

        $detailCount = 0;
        $expectedConsecutive = 1;

        foreach ($lines as $lineNumber => $line) {
            $physicalLine = $lineNumber + 1;
            $fields = explode('|', $line);
            $type = $fields[0] ?? '';

            if ($line !== rtrim($line, "\x00\x1A")) {
                $errors[] = $this->error('STRUCT005', $physicalLine, null, 'El registro contiene caracteres especiales de fin de archivo o registro.');
            }

            if ($type === '1') {
                if ($physicalLine !== 1) {
                    $errors[] = $this->error('STRUCT006', $physicalLine, 0, 'El registro tipo 1 debe ser único y aparecer primero.');
                }
                continue;
            }

            if ($type !== '2') {
                $errors[] = $this->error('STRUCT007', $physicalLine, 0, 'Tipo de registro no permitido. Se esperaba 1 o 2.');
                continue;
            }

            $detailCount++;
            if (count($fields) !== count($variables)) {
                $errors[] = $this->error('STRUCT008', $physicalLine, null, sprintf('El registro tipo 2 contiene %d campos; se esperaban %d.', count($fields), count($variables)));
                continue;
            }

            $consecutive = $fields[1] ?? '';
            if ($consecutive !== (string) $expectedConsecutive) {
                $errors[] = $this->error('STRUCT009', $physicalLine, 1, sprintf('Consecutivo inválido. Se esperaba %d y se recibió %s.', $expectedConsecutive, $consecutive));
            }
            $expectedConsecutive++;

            foreach ($variables as $variableNo => $definition) {
                $value = $fields[$variableNo] ?? '';
                $length = (int) ($definition['length'] ?? 0);
                $typeDefinition = strtoupper((string) ($definition['type'] ?? ''));

                if ($length > 0 && strlen($value) > $length) {
                    $errors[] = $this->error('STRUCT010', $physicalLine, $variableNo, sprintf('La variable %d supera la longitud máxima de %d.', $variableNo, $length), $value);
                }
                if ($value === '') {
                    continue;
                }
                if ($typeDefinition === 'N' && !preg_match('/^\d+$/', $value)) {
                    $errors[] = $this->error('STRUCT011', $physicalLine, $variableNo, 'La variable numérica contiene caracteres no numéricos.', $value);
                }
                if ($typeDefinition === 'D' && !preg_match('/^\d+(?:\.\d+)?$/', $value)) {
                    $errors[] = $this->error('STRUCT012', $physicalLine, $variableNo, 'La variable decimal no tiene un formato numérico válido.', $value);
                }
                if ($typeDefinition === 'F') {
                    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                    if ($date === false || $date->format('Y-m-d') !== $value) {
                        $errors[] = $this->error('STRUCT013', $physicalLine, $variableNo, 'La fecha no cumple el formato AAAA-MM-DD.', $value);
                    }
                }
            }
        }

        $declared = $control[4] ?? '';
        if (preg_match('/^\d+$/', $declared) && (int) $declared !== $detailCount) {
            $errors[] = $this->error('STRUCT014', 1, 4, sprintf('El registro de control declara %d registros de detalle, pero se encontraron %d.', (int) $declared, $detailCount), $declared);
        }

        return $errors;
    }

    private function error(string $code, ?int $line, ?int $variable, string $message, ?string $value = null): array
    {
        return [
            'code' => $code,
            'severity' => 'ERROR',
            'line' => $line,
            'variable' => $variable,
            'message' => $message,
            'value' => $value,
        ];
    }
}
