<?php

declare(strict_types=1);

namespace App\Domain\Res202\Correction;

final class RpedCorrectionCatalog
{
    public static function available(): array
    {
        return [
            'Error220' => ['action' => 'scientific_notation_to_integer', 'label' => 'Convertir notación científica a número entero', 'description' => 'Convierte valores en notación científica a su representación decimal exacta.', 'automatic' => true],
            'Error640' => ['action' => 'set_hemoglobin_result_998', 'label' => 'Registrar 998 en resultado de hemoglobina', 'description' => 'Mujeres de 10 a 17 años con comodín en fecha de toma de hemoglobina: variable 104 = 998.', 'automatic' => true],
            'Error638' => ['action' => 'correct_error_638', 'label' => 'Corregir fecha de toma de hemoglobina', 'description' => 'Para mujeres de 10 a 17 años, si variable 103 = 1845-01-01, se corrige a 1800-01-01 y variable 104 = 998.', 'automatic' => true],
            'Error617' => ['action' => 'set_ldl_date_from_hemoglobin', 'label' => 'Ajustar correspondencia entre variables 72 y 92', 'description' => 'Variable 72 es la fecha de toma de laboratorio y variable 92 el resultado. Si 72 = 1800-01-01, 92 = 998; si 72 = 1845-01-01, 92 = 0. Viceversa, 92 = 998 ajusta 72 a 1800-01-01 y 92 = 0 ajusta 72 a 1845-01-01.', 'automatic' => true],
            'Error618' => ['action' => 'set_ldl_date_from_hemoglobin', 'label' => 'Ajustar correspondencia entre variables 72 y 92', 'description' => 'Variable 72 es la fecha de toma de laboratorio y variable 92 el resultado. Si 72 = 1800-01-01, 92 = 998; si 72 = 1845-01-01, 92 = 0. Viceversa, 92 = 998 ajusta 72 a 1800-01-01 y 92 = 0 ajusta 72 a 1845-01-01.', 'automatic' => true],
            'Error021' => ['action' => 'set_variable_90_from_variable_2', 'label' => 'Copiar variable 2 a variable 90', 'description' => 'Para Error021, la variable 90 toma el código de habilitación reportado en la variable 2.', 'automatic' => true],
            'Error076' => ['action' => 'normalize_variable_90_toggle', 'label' => 'Corregir variable 90', 'description' => 'Variable 90: 999 se corrige a 0 y 0 se corrige a 999.', 'automatic' => true],
            'Error082' => ['action' => 'set_variable_93_1800', 'label' => 'Alternar variable 93 entre fechas comodín', 'description' => 'Variable 93: 1800-01-01 se corrige a 1845-01-01 y 1845-01-01 se corrige a 1800-01-01.', 'automatic' => true],
            'Error131' => ['action' => 'correct_error_131', 'label' => 'Corregir fecha y agudeza visual según edad', 'description' => 'Usa la fecha de la variable 29 para la variable 62 sin modificar la variable 29. Si la variable 29 es 1800-01-01, las variables 27 y 28 toman 21 y la variable 62 toma 1800-01-01. Para menores de 3 años, el tamizaje de agudeza visual no aplica: variables 27 y 28 = 0 y variable 62 = 1845-01-01.', 'automatic' => true],
            'Error132' => ['action' => 'copy_variable_29_to_63', 'label' => 'Copiar variable 29 a variable 63', 'description' => 'La variable 63 toma exactamente el contenido de la variable 29.', 'automatic' => true],
            'Error142' => ['action' => 'copy_variable_29_to_76', 'label' => 'Copiar variable 76 según variable 29', 'description' => 'La variable 76 toma exactamente el contenido de la variable 29.', 'automatic' => true],
            'Error144' => ['action' => 'copy_variable_29_to_80', 'label' => 'Copiar variable 80 según variable 29', 'description' => 'La variable 80 toma exactamente el contenido de la variable 29.', 'automatic' => true],
            'Error145' => ['action' => 'copy_variable_29_to_82', 'label' => 'Copiar variable 82 según variable 29', 'description' => 'La variable 82 toma exactamente el contenido de la variable 29.', 'automatic' => true],
            'Error153' => ['action' => 'copy_variable_29_to_103', 'label' => 'Copiar variable 29 a variable 103', 'description' => 'La variable 103 toma exactamente el contenido de la variable 29.', 'automatic' => true],
            'Error642' => ['action' => 'copy_variable_29_to_105', 'label' => 'Copiar variable 29 a variable 105', 'description' => 'La variable 105 toma exactamente el contenido de la variable 29.', 'automatic' => true],
            'Error150' => ['action' => 'copy_variable_29_to_96', 'label' => 'Copiar variable 96 según variable 29', 'description' => 'La variable 96 toma exactamente el contenido de la variable 29.', 'automatic' => true],
            'Error064' => ['action' => 'toggle_variable_71', 'label' => 'Alternar variable 71', 'description' => 'Variable 71: 1 se corrige a 0 y 0 se corrige a 1. Si el valor reportado es 21 en Error064, se normaliza a 1.', 'automatic' => true],
            'Error084' => ['action' => 'set_variable_94_zero', 'label' => 'Registrar 0 en variable 94', 'description' => 'Variable 94 = 0.', 'automatic' => true],
            'Error223' => ['action' => 'set_variable_14_21', 'label' => 'Registrar 21 en variable 14', 'description' => 'Variable 14 = 21.', 'automatic' => true],
            'Error227' => ['action' => 'set_variable_16_zero', 'label' => 'Registrar 0 en variable 16', 'description' => 'Variable 16 = 0.', 'automatic' => true],
            'Error304' => ['action' => 'toggle_variable_53', 'label' => 'Alternar variable 53 entre fechas comodín', 'description' => 'Variable 53: 1800-01-01 se corrige a 1845-01-01 y 1845-01-01 se corrige a 1800-01-01.', 'automatic' => true],
            'Error305' => ['action' => 'toggle_variable_53', 'label' => 'Alternar variable 53 entre fechas comodín', 'description' => 'Variable 53: 1800-01-01 se corrige a 1845-01-01 y 1845-01-01 se corrige a 1800-01-01.', 'automatic' => true],
            'Error308' => ['action' => 'toggle_variable_54', 'label' => 'Alternar variable 54', 'description' => 'Variable 54: 21 se corrige a 0 y 0 se corrige a 21.', 'automatic' => true],
            'Error329' => ['action' => 'toggle_variable_71', 'label' => 'Alternar variable 71', 'description' => 'Variable 71: 1 se corrige a 0 y 0 se corrige a 1. Si el valor reportado es 21 en Error064, se normaliza a 1.', 'automatic' => true],
            'Error536' => ['action' => 'toggle_variable_36', 'label' => 'Alternar variable 36', 'description' => 'Variable 36: 0 se corrige a 21 y 21 se corrige a 0.', 'automatic' => true],
            'Error558' => ['action' => 'set_variable_52_1845', 'label' => 'Corregir variable 52 a 1845-01-01', 'description' => 'Para Error558, cuando la variable 52 está en 1800-01-01 se corrige únicamente a 1845-01-01.', 'automatic' => true],
            'Error562' => ['action' => 'set_variable_52_1845', 'label' => 'Corregir variable 52 a 1845-01-01', 'description' => 'Para Error562, cuando la variable 52 está en 1800-01-01 se corrige únicamente a 1845-01-01.', 'automatic' => true],
            'Error566' => ['action' => 'set_variable_52_1845', 'label' => 'Corregir variable 52 a 1845-01-01', 'description' => 'Para Error566, cuando la variable 52 está en 1800-01-01 se corrige únicamente a 1845-01-01.', 'automatic' => true],
            'Error570' => ['action' => 'set_variable_52_1845', 'label' => 'Corregir variable 52 a 1845-01-01', 'description' => 'Para Error570, cuando la variable 52 está en 1800-01-01 se corrige únicamente a 1845-01-01.', 'automatic' => true],
            'Error597' => ['action' => 'set_variable_76_1845', 'label' => 'Corregir variables 76 y 102 según edad', 'description' => 'Menores de 6 meses: variable 76 = 1845-01-01 y variable 102 = 0. Desde 6 meses: si 76 = 1845-01-01 se corrige a 1800-01-01 y variable 102 = 21; si 76 = 1800-01-01, variable 102 = 21; una fecha real se conserva.', 'automatic' => true],
            'Error621' => ['action' => 'set_variable_93_1800', 'label' => 'Alternar variable 93 entre fechas comodín', 'description' => 'Variable 93: 1800-01-01 se corrige a 1845-01-01 y 1845-01-01 se corrige a 1800-01-01.', 'automatic' => true],
            'Error622' => ['action' => 'set_variable_93_1800', 'label' => 'Alternar variable 93 entre fechas comodín', 'description' => 'Variable 93: 1800-01-01 se corrige a 1845-01-01 y 1845-01-01 se corrige a 1800-01-01.', 'automatic' => true],
            'Error362' => ['action' => 'toggle_variable_96', 'label' => 'Alternar variable 96 entre fechas comodín', 'description' => 'Variable 96: 1845-01-01 se corrige a 1800-01-01 y 1800-01-01 se corrige a 1845-01-01.', 'automatic' => true],
            'Error669' => ['action' => 'set_variable_77_zero', 'label' => 'Registrar 0 en variable 77', 'description' => 'Variable 77 = 0.', 'automatic' => true],
            'Error670' => ['action' => 'set_variable_77_one', 'label' => 'Corregir variable 77 según edad', 'description' => 'Para Error670, la variable 77 se corrige a 1 cuando el error corresponde al rango de edad definido en el anexo para suministro de hierro en la primera infancia.', 'automatic' => true],
            'Error328' => ['action' => 'correct_error_328', 'label' => 'Corregir variable 70 según edad', 'description' => 'Para Error328, cuando la edad reportada está entre 6 y 27 meses y la variable 70 = 0, se cambia automáticamente a 1. Si variable 16 es 4 o 5, variable 52 toma la fecha de variable 29.', 'automatic' => true],
            'Error379' => ['action' => 'correct_error_379', 'label' => 'Corregir variables relacionadas', 'description' => 'Registra los valores indicados en las variables 35, 49, 50, 56, 58, 59, 60 y 61.', 'automatic' => true],
            'Error503' => ['action' => 'set_variable_52_from_29_when_16_4_5', 'label' => 'Corregir variable 52 según variable 16', 'description' => 'Si variable 16 es 4 o 5, variable 52 toma la fecha de variable 29.', 'automatic' => true],
            'Error524' => ['action' => 'correct_error_524', 'label' => 'Corregir variables 62 y 27', 'description' => 'Si variable 29 es fecha válida, variable 62 toma variable 29; si variable 29 es 1800-01-01, variable 27 = 21.', 'automatic' => true],
            'Error525' => ['action' => 'set_variable_27_21_if_29_1800', 'label' => 'Corregir variable 27 según variable 29', 'description' => 'Si variable 29 = 1800-01-01, variable 27 = 21.', 'automatic' => true],
            'Error528' => ['action' => 'correct_error_528', 'label' => 'Corregir variables 62 y 28', 'description' => 'Si variable 29 es fecha válida, variable 62 toma variable 29; si variable 29 es 1800-01-01, variable 28 = 21.', 'automatic' => true],
            'Error529' => ['action' => 'set_variable_28_21_if_29_1800', 'label' => 'Corregir variable 28 según variable 29', 'description' => 'Si variable 29 = 1800-01-01, variable 28 = 21.', 'automatic' => true],
            'Error550' => ['action' => 'correct_error_550', 'label' => 'Corregir variables 27, 28, 62, 63 y 40 según regla', 'description' => 'Las variables 27 y 28 no se interpretan como fechas. Si variable 29 es fecha real, variable 62 debe ser fecha real y, si no lo es, toma variable 29. Si variable 29 no es fecha real, variable 62 conserva el valor de 29 y 27/28 = 21. Además, según edad al corte: hasta 12 años -> 63 = 1800-01-01 y 40 = 21; mayores de 12 -> 63 = 1845-01-01 y 40 = 0.', 'automatic' => true],
            'Error560' => ['action' => 'correct_child_development_43_46', 'label' => 'Corregir variables 43 a 46 y 52', 'description' => 'Hasta 7 años: si variable 29 es válida, 43-46 = 5 y 52 = variable 29; si variable 29 = 1800-01-01, 43-46 = 21 y 52 = 1800-01-01.', 'automatic' => true],
            'Error564' => ['action' => 'correct_child_development_43_46', 'label' => 'Corregir variables 43 a 46 y 52', 'description' => 'Aplica la misma lógica de variables 43 a 46 y 52 definida en la matriz.', 'automatic' => true],
            'Error568' => ['action' => 'correct_child_development_43_46', 'label' => 'Corregir variables 43 a 46 y 52', 'description' => 'Aplica la misma lógica de variables 43 a 46 y 52 definida en la matriz.', 'automatic' => true],
            'Error572' => ['action' => 'correct_child_development_43_46', 'label' => 'Corregir variables 43 a 46 y 52', 'description' => 'Aplica la misma lógica de variables 43 a 46 y 52 definida en la matriz.', 'automatic' => true],
            'Error635' => ['action' => 'normalize_variable_102_length', 'label' => 'Normalizar variable 102 a 12 dígitos', 'description' => 'Conserva 11 o 12 dígitos y no agrega automáticamente el cero inicial.', 'automatic' => true],
            'Error639' => ['action' => 'correct_error_639', 'label' => 'Corregir variables 103 y 104 según edad', 'description' => 'Variable 103 es la fecha de toma de laboratorio y variable 104 es el resultado. Entre 10 y 17 años: 103 real exige 104 distinto de 0 y 998; 103 = 1800-01-01 corresponde a 104 = 998; 103 = 1845-01-01 corresponde a 104 = 0. Viceversa, 104 = 998 ajusta 103 a 1800-01-01 y 104 = 0 ajusta 103 a 1845-01-01. Fuera del rango de edad, se corrige 103 = 1845-01-01 y 104 = 0.', 'automatic' => true],
            'Error678' => ['action' => 'normalize_variable_102_length', 'label' => 'Normalizar variable 102 a 12 dígitos', 'description' => 'Conserva 11 o 12 dígitos y no agrega automáticamente el cero inicial.', 'automatic' => true],
            'Error634' => ['action' => 'omit_error', 'label' => 'Omitir error', 'description' => 'La matriz indica omitir el error; no modifica el dato.', 'automatic' => false],
            'STRUCT011' => ['action' => 'omit_error', 'label' => 'Omitir error', 'description' => 'La matriz indica omitir el error; no modifica el dato.', 'automatic' => false],
            'Warning674' => ['action' => 'omit_error', 'label' => 'No modificar variables 29 ni 31', 'description' => 'Las variables 29 y 31 no se modifican automáticamente.', 'automatic' => false],
            'Warning675' => ['action' => 'omit_error', 'label' => 'No modificar variables 29 ni 31', 'description' => 'Las variables 29 y 31 no se modifican automáticamente.', 'automatic' => false],
        ];
    }

    public static function forCode(string $code): ?array
    {
        return self::available()[$code] ?? null;
    }
}
