<?php

declare(strict_types=1);

namespace App\Domain\Res202\Correction;

final class RpedCorrectionCatalog
{
    public static function available(): array
    {
        return [
            'Error220' => [
                'action' => 'scientific_notation_to_integer',
                'label' => 'Convertir notación científica a número entero',
                'description' => 'Convierte únicamente valores como 2.60874E+13 a su representación decimal exacta, sin cambiar el valor matemático.',
                'automatic' => true,
            ],
            'Error640' => [
                'action' => 'set_hemoglobin_result_998',
                'label' => 'Registrar 998 en resultado de hemoglobina',
                'description' => 'Para mujeres de 10 a 17 años con comodín de no realización o sin dato en la fecha de toma de hemoglobina, registra 998 en el resultado.',
                'automatic' => true,
            ],
        ];
    }

    public static function forCode(string $code): ?array
    {
        return self::available()[$code] ?? null;
    }
}
