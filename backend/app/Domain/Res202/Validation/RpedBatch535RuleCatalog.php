<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

/** Reglas RPED Error535-Error540 implementadas desde lineamientos v8. */
final class RpedBatch535RuleCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function executable(): array
    {
        return [
            self::forbidden('Error535','Si registra Fecha de colonoscopia válida, registre el resultado de colonoscopia de tamizaje',['all'=>[['field'=>66,'op'=>'gt','value'=>'1900-01-01'],['field'=>36,'op'=>'in','values'=>['0','21']]]],36),
            self::forbidden('Error536','Si registró comodín de no realización o sin dato en Fecha de colonoscopia, la edad debe estar entre 50 y 75 años y resultado de colonoscopia debe ser 21',['all'=>[['field'=>66,'op'=>'in','values'=>['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01']],['any'=>[['age_months'=>true,'op'=>'lt','value'=>600],['age_months'=>true,'op'=>'gt','value'=>900],['field'=>36,'op'=>'neq','value'=>'21']]]]],36),
            self::forbidden('Error537','Si registró comodín de no aplica en Fecha de colonoscopia, el resultado debe ser 0 y la edad debe ser menor o igual a 50 años o mayor a 75 años',['all'=>[['field'=>66,'op'=>'eq','value'=>'1845-01-01'],['any'=>[['field'=>36,'op'=>'neq','value'=>'0'],['all'=>[['age_months'=>true,'op'=>'gte','value'=>600],['age_months'=>true,'op'=>'lte','value'=>900]]]]]],36),
            self::forbidden('Error538','Error en valores permitidos - Resultado colonoscopia de tamizaje',['field'=>36,'op'=>'not_in','values'=>['0','2','3','4','5','6','21']],36),
            self::forbidden('Error539','Si registra Resultado de tamizaje auditivo neonatal, debe registrar fecha de tamizaje',['all'=>[['field'=>37,'op'=>'in','values'=>['4','5']],['field'=>69,'op'=>'lte','value'=>'1900-01-01']]],69),
            self::forbidden('Error540','Error en valores permitidos - Resultado de tamizaje auditivo neonatal',['field'=>37,'op'=>'not_in','values'=>['0','4','5','21']],37),
        ];
    }

    private static function forbidden(string $code,string $message,array $when,int $variable):array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'forbidden_when','variable'=>$variable,'when'=>$when,'message'=>$message];
    }
}
