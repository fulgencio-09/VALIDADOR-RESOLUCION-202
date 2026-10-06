<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

/** Reglas RPED Error600-Error631 traducidas directamente de Lineamientos v8. */
final class RpedBatch600RuleCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function executable(): array
    {
        $w = self::wildcards();

        return [
            self::f('Error600', 78, ['all'=>[
                ['field'=>79,'op'=>'eq','value'=>'0'],
                ['field'=>78,'op'=>'neq','value'=>'1845-01-01'],
            ]]),
            self::inValues('Error601', 79, ['0','4','5','21'], 'Error en valores permitidos - Resultado antígeno de superficie hepatitis B'),

            self::f('Error602', 81, ['all'=>[
                ['field'=>80,'op'=>'in','values'=>array_slice($w,0,6)],
                ['field'=>81,'op'=>'neq','value'=>'21'],
            ]]),
            self::f('Error603', 80, ['all'=>[
                ['field'=>81,'op'=>'eq','value'=>'0'],
                ['field'=>80,'op'=>'neq','value'=>'1845-01-01'],
            ]]),
            self::inValues('Error604', 81, ['0','4','5','21'], 'Error en valores permitidos - Resultado de prueba de tamizaje para sífilis'),

            self::f('Error605', 83, ['all'=>[
                ['field'=>82,'op'=>'in','values'=>array_slice($w,0,6)],
                ['field'=>83,'op'=>'neq','value'=>'21'],
            ]]),
            self::f('Error606', 82, ['all'=>[
                ['field'=>82,'op'=>'eq','value'=>'1845-01-01'],
                ['field'=>83,'op'=>'neq','value'=>'0'],
            ]]),
            self::inValues('Error607', 83, ['0','4','5','21'], 'Error en valores permitidos - Resultado de la prueba para VIH'),

            self::inValues('Error608', 85, ['0','4','5','21'], 'Error en valores permitidos - Resultado de TSH neonatal'),
            self::f('Error609', 84, ['all'=>[
                ['field'=>85,'op'=>'eq','value'=>'21'],
                ['field'=>84,'op'=>'not_in','values'=>array_slice($w,0,6)],
            ]]),

            self::f('Error610', 87, ['all'=>[
                ['field'=>86,'op'=>'in','values'=>['1','2','3','4']],
                ['field'=>87,'op'=>'lte','value'=>'1900-01-01'],
            ]]),
            self::f('Error611', 88, ['all'=>[
                ['field'=>86,'op'=>'in','values'=>['1','4']],
                ['field'=>88,'op'=>'not_in','values'=>['1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18']],
            ]]),
            self::f('Error612', 88, ['all'=>[
                ['field'=>86,'op'=>'in','values'=>['2','3']],
                ['field'=>88,'op'=>'not_in','values'=>['19','20']],
            ]]),
            self::f('Error613', 87, ['all'=>[
                ['field'=>86,'op'=>'eq','value'=>'0'],
                ['any'=>[
                    ['field'=>87,'op'=>'neq','value'=>'1845-01-01'],
                    ['field'=>88,'op'=>'neq','value'=>'0'],
                ]],
            ]]),
            self::f('Error614', 87, ['all'=>[
                ['field'=>86,'op'=>'eq','value'=>'21'],
                ['any'=>[
                    ['field'=>87,'op'=>'not_in','values'=>array_slice($w,0,6)],
                    ['field'=>88,'op'=>'neq','value'=>'21'],
                ]],
            ]]),
            self::f('Error615', 88, ['all'=>[
                ['field'=>89,'op'=>'eq','value'=>'4'],
                ['field'=>88,'op'=>'neq','value'=>'18'],
            ]]),
            self::inValues('Error616', 88, array_map('strval', range(0,21)), 'Error en valores permitidos - Resultado tamizaje de cáncer de cuello'),

            self::f('Error617', 92, ['all'=>[
                ['field'=>72,'op'=>'gt','value'=>'1900-01-01'],
                ['any'=>[
                    ['field'=>92,'op'=>'eq','value'=>'0'],
                    ['field'=>92,'op'=>'gte','value'=>'998'],
                ]],
            ]]),
            self::f('Error618', 92, ['any'=>[
                ['all'=>[
                    ['field'=>72,'op'=>'in','values'=>array_slice($w,0,6)],
                    ['field'=>92,'op'=>'neq','value'=>'998'],
                ]],
                ['all'=>[
                    ['field'=>92,'op'=>'eq','value'=>'998'],
                    ['field'=>72,'op'=>'not_in','values'=>array_slice($w,0,6)],
                ]],
            ]]),
            self::f('Error619', 92, ['all'=>[
                ['field'=>72,'op'=>'eq','value'=>'1845-01-01'],
                ['any'=>[
                    ['field'=>92,'op'=>'neq','value'=>'0'],
                    ['age_years'=>true,'op'=>'gte','value'=>'29'],
                ]],
            ]]),

            self::inValues('Error620', 94, ['0','1','3','4','5','6','21'], 'Error en valores permitidos - Resultado de biopsia cervicouterina'),
            self::f('Error621', 93, ['all'=>[
                ['field'=>94,'op'=>'eq','value'=>'0'],
                ['field'=>93,'op'=>'neq','value'=>'1845-01-01'],
            ]]),
            self::f('Error622', 93, ['all'=>[
                ['field'=>94,'op'=>'eq','value'=>'21'],
                ['field'=>93,'op'=>'not_in','values'=>array_slice($w,0,6)],
            ]]),

            self::f('Error623', 95, ['all'=>[
                ['field'=>111,'op'=>'gt','value'=>'1900-01-01'],
                ['any'=>[
                    ['field'=>95,'op'=>'eq','value'=>'0'],
                    ['field'=>95,'op'=>'gte','value'=>'998'],
                ]],
            ]]),
            self::f('Error624', 95, ['any'=>[
                ['all'=>[
                    ['field'=>111,'op'=>'in','values'=>array_slice($w,0,6)],
                    ['field'=>95,'op'=>'neq','value'=>'998'],
                ]],
                ['all'=>[
                    ['field'=>95,'op'=>'eq','value'=>'998'],
                    ['field'=>111,'op'=>'not_in','values'=>array_slice($w,0,6)],
                ]],
            ]]),
            self::f('Error625', 95, ['all'=>[
                ['field'=>111,'op'=>'eq','value'=>'1845-01-01'],
                ['any'=>[
                    ['field'=>95,'op'=>'neq','value'=>'0'],
                    ['age_years'=>true,'op'=>'gte','value'=>'29'],
                ]],
            ]]),

            self::f('Error626', 97, ['all'=>[
                ['field'=>10,'op'=>'eq','value'=>'F'],
                ['age_years'=>true,'op'=>'gte','value'=>'50'],
                ['field'=>96,'op'=>'in','values'=>array_slice($w,0,6)],
                ['field'=>97,'op'=>'neq','value'=>'21'],
            ]]),
            self::f('Error627', 97, ['all'=>[
                ['field'=>96,'op'=>'eq','value'=>'1845-01-01'],
                ['field'=>97,'op'=>'neq','value'=>'0'],
            ]]),
            self::inValues('Error628', 97, ['0','1','2','3','4','5','6','7','21'], 'Error en valores permitidos - Resultado mamografía'),

            self::f('Error629', 98, ['all'=>[
                ['field'=>118,'op'=>'gt','value'=>'1900-01-01'],
                ['any'=>[
                    ['field'=>98,'op'=>'eq','value'=>'0'],
                    ['field'=>98,'op'=>'gte','value'=>'998'],
                ]],
            ]]),
            self::f('Error630', 98, ['any'=>[
                ['all'=>[
                    ['field'=>118,'op'=>'in','values'=>array_slice($w,0,6)],
                    ['field'=>98,'op'=>'neq','value'=>'998'],
                ]],
                ['all'=>[
                    ['field'=>98,'op'=>'eq','value'=>'998'],
                    ['field'=>118,'op'=>'not_in','values'=>array_slice($w,0,6)],
                ]],
            ]]),
            self::f('Error631', 98, ['all'=>[
                ['field'=>118,'op'=>'eq','value'=>'1845-01-01'],
                ['any'=>[
                    ['field'=>98,'op'=>'neq','value'=>'0'],
                    ['age_years'=>true,'op'=>'gte','value'=>'29'],
                ]],
            ]]),
        ];
    }

    private static function f(string $code, int $variable, array $when, string $severity = 'ERROR'): array
    {
        return ['code'=>$code,'severity'=>$severity,'operation'=>'forbidden_when','variable'=>$variable,'when'=>$when,'message'=>'Validación RPED v8 no cumplida: '.$code];
    }

    private static function inValues(string $code, int $variable, array $values, string $message): array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'in','variable'=>$variable,'values'=>$values,'message'=>$message];
    }

    /** @return array<int,string> */
    private static function wildcards(): array
    {
        return ['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'];
    }
}
