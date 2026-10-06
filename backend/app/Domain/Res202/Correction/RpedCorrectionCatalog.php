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
                'automatic' => false,
            ],
        ];
    }

    public static function forCode(string $code): ?array
    {
        return self::available()[$code] ?? null;
    }
}
