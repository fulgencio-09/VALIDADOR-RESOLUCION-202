<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

final class RpedRuleCatalog
{
    /**
     * Primer lote ejecutable, derivado literalmente de las validaciones del
     * Lineamientos RPED de la versión 8 del anexo técnico.
     *
     * Las demás reglas permanecen en el catálogo de 395 códigos y se irán
     * incorporando por familias de operación, sin marcarlas como ejecutables
     * hasta que exista una implementación verificable.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function executable(): array
    {
        return [
            ['code' => 'Error020', 'severity' => 'ERROR', 'operation' => 'required', 'variable' => 9, 'message' => 'La fecha de nacimiento es requerida'],
            ['code' => 'Error030', 'severity' => 'ERROR', 'operation' => 'equals_when_any', 'variable' => 10, 'when_variable' => 14, 'when_value' => '1|2|21', 'expected' => 'F', 'message' => 'Si registra 1, 2 ó 21 en la variable Gestante, el sexo debe ser F'],
            ['code' => 'Error041', 'severity' => 'ERROR', 'operation' => 'equals_when', 'variable' => 29, 'when_variable' => 30, 'when_value' => '999', 'expected' => '1800-01-01', 'message' => 'Si no registra el peso de la persona no debe registrar fecha de medición'],
            ['code' => 'Error043', 'severity' => 'ERROR', 'operation' => 'equals_when', 'variable' => 31, 'when_variable' => 32, 'when_value' => '999', 'expected' => '1800-01-01', 'message' => 'Si no registra la talla de la persona no debe registrar fecha de medición'],
            ['code' => 'Error653', 'severity' => 'ERROR', 'operation' => 'in', 'variable' => 113, 'values' => ['1', '2', '3', '4', '21'], 'message' => 'Error en valores permitidos - Resultado de baciloscopia diagnóstico'],
            ['code' => 'Error655', 'severity' => 'ERROR', 'operation' => 'in', 'variable' => 114, 'values' => ['0', '4', '5', '6', '21'], 'message' => 'Error en valores permitidos -Clasificación del riesgo cardiovascular'],
            ['code' => 'Error656', 'severity' => 'ERROR', 'operation' => 'in', 'variable' => 115, 'values' => ['0'], 'message' => 'Error en valores permitidos - Tratamiento para sífilis gestacional'],
            ['code' => 'Error657', 'severity' => 'ERROR', 'operation' => 'in', 'variable' => 116, 'values' => ['0'], 'message' => 'Error en valores permitidos - Tratamiento para sífilis congénita'],
            ['code' => 'Error665', 'severity' => 'ERROR', 'operation' => 'in', 'variable' => 117, 'values' => ['0', '4', '5', '6', '21'], 'message' => 'Error en valores permitidos -Clasificación de riesgo metabólico'],
            ['code' => 'Error676', 'severity' => 'ERROR', 'operation' => 'length_by_value', 'variable' => 4, 'selector_variable' => 3, 'length_map' => [
                'CC' => [['max' => 10]], 'TI' => [['max' => 11]], 'CE' => [['min' => 3, 'max' => 7]],
                'CD' => [['max' => 11]], 'PA' => [['min' => 3, 'max' => 16]], 'SC' => [['max' => 9]],
                'PE' => [['min' => 3, 'max' => 15]],
            ], 'message' => 'La longitud del número de identificación no corresponde con el tipo de identificación'],
            ['code' => 'Error677', 'severity' => 'ERROR', 'operation' => 'date_before', 'variable' => 9, 'date' => '1900-01-01', 'message' => 'No se permite el registro de estos comodines en Fecha Nacimiento'],
            ['code' => 'Error678', 'severity' => 'ERROR', 'operation' => 'length_range_by_value', 'variable' => 102, 'allowed_values' => ['0', '21'], 'allowed_lengths' => [12], 'allowed_pattern' => '/^\d{12}$/', 'message' => 'Solo se permite el registro de los valores 0, 21 o un valor de 12 dígitos de longitud'],
        ];
    }
}
