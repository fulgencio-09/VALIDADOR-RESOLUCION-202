<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

/**
 * Validaciones internas para reglas cuya consulta externa (REPS) queda abierta.
 * No se restringe el valor de V2 a un código fijo: se valida su consistencia
 * con V90 cuando V89 identifica una IPS distinta de 0 y 999.
 */
final class RpedExternalConsistencyRuleCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function executable(): array
    {
        return [
            [
                'code' => 'Error021',
                'severity' => 'ERROR',
                'operation' => 'forbidden_when',
                'variable' => 90,
                'when' => [
                    'all' => [
                        ['field' => 89, 'op' => 'neq', 'value' => '999'],
                        ['field' => 89, 'op' => 'neq', 'value' => '0'],
                        ['field' => 90, 'op' => 'neq_field', 'value' => 2],
                    ],
                ],
                'message' => 'Cuando la variable 89 es diferente de 0 y 999, la variable 90 debe ser igual a la variable 2.',
            ],
        ];
    }
}
