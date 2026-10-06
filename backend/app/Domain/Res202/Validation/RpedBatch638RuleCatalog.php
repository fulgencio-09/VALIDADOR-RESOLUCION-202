<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

/** Reglas RPED Error638-Error652 implementadas desde lineamientos v8. */
final class RpedBatch638RuleCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function executable(): array
    {
        return [
            self::forbidden('Error638','Si es mujer entre 10 y 17 años debe registrar una Fecha de toma hemoglobina diferente de 1845-01-01',['all'=>[['field'=>10,'op'=>'eq','value'=>'F'],['age_months'=>true,'op'=>'gte','value'=>120],['age_months'=>true,'op'=>'lte','value'=>204],['field'=>103,'op'=>'eq','value'=>'1845-01-01']]],103),
            self::forbidden('Error639','Si registró Fecha de toma hemoglobina válida, registre el resultado de la hemoglobina',['all'=>[['field'=>103,'op'=>'gt','value'=>'1900-01-01'],['any'=>[['field'=>104,'op'=>'eq','value'=>'0'],['field'=>104,'op'=>'gte','value'=>'99']]]]],104),
            self::forbidden('Error640','Si es mujer entre 10 y 17 años y registró un comodín de no realización o sin dato en Fecha toma de hemoglobina, registre 998 en Resultado',['all'=>[['field'=>10,'op'=>'eq','value'=>'F'],['age_months'=>true,'op'=>'gte','value'=>120],['age_months'=>true,'op'=>'lte','value'=>204],['field'=>103,'op'=>'in','values'=>['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01']],['field'=>104,'op'=>'neq','value'=>'998']]],104),
            self::forbidden('Error641','Registró no aplica en Fecha toma de hemoglobina, registre 0 en Resultado de hemoglobina',['all'=>[['field'=>103,'op'=>'eq','value'=>'1845-01-01'],['field'=>104,'op'=>'neq','value'=>'0']]],104),
            ['code'=>'Error642','severity'=>'ERROR','operation'=>'date_after_cutoff','variable'=>105,'message'=>'Fecha de toma de glicemia basal es mayor a la fecha de corte'],
            ['code'=>'Error643','severity'=>'ERROR','operation'=>'date_before_birth','variable'=>105,'birth_variable'=>9,'inclusive'=>true,'message'=>'Fecha de toma de glicemia basal es menor o igual a la fecha de nacimiento'],
            self::forbidden('Error645','Si registró un comodín de no realización en Fecha toma creatinina, registre 998 en Resultado y viceversa',['any'=>[
                ['all'=>[['field'=>106,'op'=>'in','values'=>['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01']],['field'=>107,'op'=>'neq','value'=>'998']]],
                ['all'=>[['field'=>107,'op'=>'eq','value'=>'998'],['field'=>106,'op'=>'not_in','values'=>['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01']]]],
            ]],107),
            self::forbidden('Error646','Registre no aplica en Fecha de toma de creatinina y Resultado de creatinina, a los menores de 29 años sin riesgo cardiovascular identificado',['all'=>[['field'=>106,'op'=>'eq','value'=>'1845-01-01'],['any'=>[['field'=>107,'op'=>'neq','value'=>'0'],['age_months'=>true,'op'=>'gte','value'=>348]]]]],106),
            self::forbidden('Error647','Error en valores permitidos - Fecha Hemoglobina Glicosilada',['field'=>108,'op'=>'neq','value'=>'1845-01-01'],108),
            self::forbidden('Error649','Si registra Fecha de toma PSA, debe ser hombre mayor de 40 años y registrar Resultado PSA',['all'=>[['field'=>73,'op'=>'gt','value'=>'1900-01-01'],['any'=>[['field'=>109,'op'=>'eq','value'=>'0'],['field'=>109,'op'=>'gte','value'=>'998'],['age_months'=>true,'op'=>'lt','value'=>480],['field'=>10,'op'=>'neq','value'=>'M']]]]],109),
            self::forbidden('Error650','Si Resultado de PSA es riesgo no evaluado, debe registrar alguno de los comodines de no realización en la fecha de PSA',['all'=>[['field'=>109,'op'=>'eq','value'=>'998'],['field'=>73,'op'=>'not_in','values'=>['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01']]]],73),
            self::forbidden('Error651','Si el sexo es F, registre no aplica en la fecha y el resultado de PSA',['all'=>[['field'=>10,'op'=>'eq','value'=>'F'],['any'=>[['field'=>109,'op'=>'neq','value'=>'0'],['field'=>73,'op'=>'neq','value'=>'1845-01-01']]]]],109),
            self::forbidden('Error652','Si es hombre menor de 40 años, debe registrar no aplica en el resultado y la fecha de PSA',['all'=>[['field'=>10,'op'=>'eq','value'=>'M'],['age_months'=>true,'op'=>'lt','value'=>480],['any'=>[['field'=>109,'op'=>'neq','value'=>'0'],['field'=>73,'op'=>'neq','value'=>'1845-01-01']]]]],109),
        ];
    }

    private static function forbidden(string $code,string $message,array $when,int $variable):array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'forbidden_when','variable'=>$variable,'when'=>$when,'message'=>$message];
    }
}
