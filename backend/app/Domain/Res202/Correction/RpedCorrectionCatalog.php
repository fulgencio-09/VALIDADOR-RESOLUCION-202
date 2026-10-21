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
            'Error617' => [
                'action' => 'set_ldl_date_from_hemoglobin',
                'label' => 'Ajustar fecha de toma LDL según resultado de hemoglobina',
                'description' => 'Ajusta automáticamente la variable 72 según el valor de la variable 92: 0 corresponde a 1845-01-01 y 998 corresponde a 1800-01-01.',
                'automatic' => true,
            ],
            'Error618' => [
                'action' => 'set_ldl_date_from_hemoglobin',
                'label' => 'Ajustar fecha de toma LDL según resultado de hemoglobina',
                'description' => 'Ajusta automáticamente la variable 72 según el valor de la variable 92: 0 corresponde a 1845-01-01 y 998 corresponde a 1800-01-01.',
                'automatic' => true,
            ],
            'Error021' => [
                'action' => 'set_variable_90_from_variable_2',
                'label' => 'Igualar variable 90 a variable 2',
                'description' => 'Cuando la variable 89 es diferente de 0 y 999, registra en la variable 90 el mismo valor de la variable 2.',
                'automatic' => true,
            ],
            'Error131' => [
                'action' => 'copy_variable_29_to_62',
                'label' => 'Corregir fecha de valoración de agudeza visual',
                'description' => 'Cuando la variable 62 es mayor a la fecha de corte, reemplaza la variable 62 por la fecha registrada en la variable 29.',
                'automatic' => true,
            ],
            'Error132' => [
                'action' => 'copy_variable_29_to_63',
                'label' => 'Corregir fecha de tamizaje VALE',
                'description' => 'Cuando la variable 63 es mayor a la fecha de corte, reemplaza la variable 63 por la fecha registrada en la variable 29.',
                'automatic' => true,
            ],
            'Error142' => [
                'action' => 'copy_variable_29_to_76',
                'label' => 'Corregir fecha de atención en salud bucal',
                'description' => 'Cuando la variable 76 es mayor a la fecha de corte, reemplaza la variable 76 por la fecha registrada en la variable 29.',
                'automatic' => true,
            ],
            'Error144' => [
                'action' => 'copy_variable_29_to_80',
                'label' => 'Corregir fecha de tamizaje para sífilis',
                'description' => 'Cuando la variable 80 es mayor a la fecha de corte, reemplaza la variable 80 por la fecha registrada en la variable 29.',
                'automatic' => true,
            ],
            'Error145' => [
                'action' => 'copy_variable_29_to_82',
                'label' => 'Corregir fecha de prueba para VIH',
                'description' => 'Cuando la variable 82 es mayor a la fecha de corte, reemplaza la variable 82 por la fecha registrada en la variable 29.',
                'automatic' => true,
            ],
            'Error153' => [
                'action' => 'copy_variable_29_to_103',
                'label' => 'Corregir fecha de toma de hemoglobina',
                'description' => 'Cuando la variable 103 es mayor a la fecha de corte, reemplaza la variable 103 por la fecha registrada en la variable 29.',
                'automatic' => true,
            ],
            'Error642' => [
                'action' => 'copy_variable_29_to_105',
                'label' => 'Corregir fecha de toma de glicemia basal',
                'description' => 'Cuando la variable 105 es mayor a la fecha de corte, reemplaza la variable 105 por la fecha registrada en la variable 29.',
                'automatic' => true,
            ],
            'Error150' => [
                'action' => 'copy_variable_29_to_96',
                'label' => 'Corregir fecha de mamografía',
                'description' => 'Cuando la variable 96 es mayor a la fecha de corte, reemplaza la variable 96 por la fecha registrada en la variable 29.',
                'automatic' => true,
            ],
            'Error526' => [
                'action' => 'set_visual_acuity_under_3_left',
                'label' => 'Corregir agudeza visual izquierda para menores de 3 años',
                'description' => 'Para menores de 3 años, registra 0 en la variable 27 y 1845-01-01 en la variable 62.',
                'automatic' => true,
            ],
            'Error530' => [
                'action' => 'set_visual_acuity_under_3_right',
                'label' => 'Corregir agudeza visual derecha para menores de 3 años',
                'description' => 'Para menores de 3 años, registra 0 en la variable 28 y 1845-01-01 en la variable 62.',
                'automatic' => true,
            ],
            'Error076' => [
                'action' => 'set_variable_90_zero',
                'label' => 'Corregir IPS de tamizaje de cáncer de cuello uterino',
                'description' => 'Si el valor reportado es 999, lo reemplaza por 0.',
                'automatic' => true,
            ],
            'Error082' => [
                'action' => 'set_variable_93_not_applicable',
                'label' => 'Corregir fecha de biopsia cervicouterina',
                'description' => 'Si la fecha reportada es 1800-01-01, la reemplaza por 1845-01-01.',
                'automatic' => true,
            ],
            'Error084' => [
                'action' => 'set_variable_94_zero',
                'label' => 'Corregir resultado de biopsia cervicouterina',
                'description' => 'Si el valor reportado es 999, lo reemplaza por 0.',
                'automatic' => true,
            ],
            'Error304' => [
                'action' => 'set_variable_53_not_applicable',
                'label' => 'Corregir fecha de asesoría en anticoncepción',
                'description' => 'Si la fecha reportada es 1800-01-01, la reemplaza por 1845-01-01.',
                'automatic' => true,
            ],
        ];
    }

    public static function forCode(string $code): ?array
    {
        return self::available()[$code] ?? null;
    }
}
