<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

/**
 * Reglas RPED aclaradas durante la validación funcional del proyecto.
 * Las condiciones aquí reflejan literalmente las reglas acordadas para v8.
 */
final class RpedClarifiedRuleCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function executable(): array
    {
        $wNoDate = ['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01'];
        $wAll = [...$wNoDate, '1845-01-01'];

        return [
            // Error572: V46 depende de la edad: >=8 años usa 0; <8 años usa 3/4/5/21.
            [
                'code'=>'Error572','severity'=>'ERROR','operation'=>'forbidden_when','variable'=>46,
                'when'=>['any'=>[
                    ['all'=>[
                        ['age_years'=>true,'op'=>'gte','value'=>'8'],
                        ['field'=>46,'op'=>'in','values'=>['3','4','5','21']],
                    ]],
                    ['all'=>[
                        ['age_years'=>true,'op'=>'lt','value'=>'8'],
                        ['field'=>46,'op'=>'eq','value'=>'0'],
                    ]],
                ]],
                'message'=>'Verifique el resultado de escala abreviada de desarrollo área de motricidad audición lenguaje, de acuerdo a la edad calculada.',
            ],

            // Error632: V101=21 exige V100=1800-01-01.
            [
                'code'=>'Error632','severity'=>'ERROR','operation'=>'forbidden_when','variable'=>101,
                'when'=>['all'=>[
                    ['field'=>100,'op'=>'neq','value'=>'1800-01-01'],
                    ['field'=>101,'op'=>'eq','value'=>'21'],
                ]],
                'message'=>'Si registra sin dato en Resultado de biopsia de mama, debe registrar sin dato en Fecha de resultado de biopsia.',
            ],
            ['code'=>'Error633','severity'=>'ERROR','operation'=>'in','variable'=>101,
                'values'=>['0','1','2','3','4','5','21'],
                'message'=>'Error en valores permitidos - Resultado de Biopsia de mama'],

            // Error634: desde 6 meses, si no hubo fecha real y se usó comodín de no realización/sin dato, V102 debe ser 21.
            [
                'code'=>'Error634','severity'=>'ERROR','operation'=>'forbidden_when','variable'=>102,
                'when'=>['all'=>[
                    ['age_months'=>true,'op'=>'gte','value'=>'6'],
                    ['field'=>76,'op'=>'in','values'=>$wNoDate],
                    ['field'=>102,'op'=>'neq','value'=>'21'],
                ]],
                'message'=>'Si registró comodín de no realización o sin dato en Fecha atención en salud bucal a partir de los 6 meses de edad, registre 21 en COP por persona.',
            ],

            // Error635: con fecha real de odontología, COP debe tener exactamente 12 dígitos.
            [
                'code'=>'Error635','severity'=>'ERROR','operation'=>'forbidden_when','variable'=>102,
                'when'=>['all'=>[
                    ['field'=>76,'op'=>'gt','value'=>'1900-01-01'],
                    ['length_of'=>102,'op'=>'neq','value'=>'12'],
                ]],
                'message'=>'Si registró Fecha atención en salud bucal por profesional en odontología, debe registrar el resultado de COP con longitud de 12 dígitos.',
            ],

            // Error636: 6 meses a <5 años; componentes 00-22 y coherencia COP. Para dentición infantil se aplica máximo 20.
            [
                'code'=>'Error636','severity'=>'ERROR','operation'=>'cop_consistency','variable'=>102,
                'when'=>[
                    ['field'=>76,'op'=>'gt','value'=>'1900-01-01'],
                    ['age_months'=>true,'op'=>'gte','value'=>'6'],
                    ['age_years'=>true,'op'=>'lt','value'=>'5'],
                ],
                'min'=>0,'max'=>22,'total_max'=>20,
                'message'=>'En personas de 6 meses a 4 años 11 meses y 29 días, cada componente del COP debe estar entre 00 y 22 y ser coherente con el total de dientes presentes (máximo operativo 20).',
            ],

            // Error637: >=5 años; componentes 00-32 y coherencia COP.
            [
                'code'=>'Error637','severity'=>'ERROR','operation'=>'cop_consistency','variable'=>102,
                'when'=>[
                    ['field'=>76,'op'=>'gt','value'=>'1900-01-01'],
                    ['age_years'=>true,'op'=>'gte','value'=>'5'],
                ],
                'min'=>0,'max'=>32,'total_max'=>32,
                'message'=>'En personas de 5 años y más, cada componente del COP debe estar entre 00 y 32 y ser coherente con el total de dientes presentes.',
            ],

            // Error666: fecha de triglicéridos no puede superar la fecha de corte.
            ['code'=>'Error666','severity'=>'ERROR','operation'=>'date_after_cutoff','variable'=>118,
                'message'=>'Fecha de toma triglicéridos es mayor a la fecha de corte.'],

            // Error667: fecha de triglicéridos <= fecha de nacimiento, excepto comodines.
            ['code'=>'Error667','severity'=>'ERROR','operation'=>'date_before_birth','variable'=>118,
                'birth_variable'=>9,'ignore_values'=>$wAll,'inclusive'=>true,
                'message'=>'Fecha de toma triglicéridos es menor a la fecha de nacimiento.'],

            // Error669: suministro de hierro distinto de 0 solo entre 24 y 63 meses.
            [
                'code'=>'Error669','severity'=>'ERROR','operation'=>'forbidden_when','variable'=>77,
                'when'=>['all'=>[
                    ['field'=>77,'op'=>'neq','value'=>'0'],
                    ['any'=>[
                        ['age_months'=>true,'op'=>'lt','value'=>'24'],
                        ['age_months'=>true,'op'=>'gt','value'=>'63'],
                    ]],
                ]],
                'message'=>'El suministro de hierro solo corresponde a población entre 24 y 63 meses de edad.',
            ],

            // Error670: 0-No aplica no puede usarse entre 24 y 63 meses.
            [
                'code'=>'Error670','severity'=>'ERROR','operation'=>'forbidden_when','variable'=>77,
                'when'=>['all'=>[
                    ['field'=>77,'op'=>'eq','value'=>'0'],
                    ['age_months'=>true,'op'=>'gte','value'=>'24'],
                    ['age_months'=>true,'op'=>'lte','value'=>'63'],
                ]],
                'message'=>'Entre 24 y 63 meses no debe registrar 0-No aplica en suministro de hierro.',
            ],

            // Error673: inspección visual positiva exige V47=0 según la regla aclarada.
            [
                'code'=>'Error673','severity'=>'ERROR','operation'=>'forbidden_when','variable'=>47,
                'when'=>['all'=>[
                    ['field'=>86,'op'=>'eq','value'=>'3'],
                    ['field'=>88,'op'=>'eq','value'=>'19'],
                    ['field'=>47,'op'=>'neq','value'=>'0'],
                ]],
                'message'=>'Si la variable 86=3 y la variable 88=19, la variable 47 debe registrar valor 0.',
            ],
        ];
    }
}
