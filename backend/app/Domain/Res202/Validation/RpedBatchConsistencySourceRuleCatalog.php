<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

/** Reglas de consistencia RPED v8 traducidas de la columna VALIDACIONES. */
final class RpedBatchConsistencySourceRuleCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function executable(): array
    {
        $w=self::wildcards();
        return [
            self::f('Error503',16,['all'=>[['field'=>16,'op'=>'in','values'=>['4','5']],['field'=>52,'op'=>'lt','value'=>'1900-01-01']]]),
            self::f('Error506',18,['all'=>[['field'=>18,'op'=>'eq','value'=>'2'],['any'=>[['field'=>113,'op'=>'neq','value'=>'4'],['field'=>112,'op'=>'neq','value'=>'1845-01-01']]]]),
            self::f('Error507',18,['all'=>[['field'=>18,'op'=>'eq','value'=>'21'],['any'=>[['field'=>113,'op'=>'neq','value'=>'21'],['field'=>112,'op'=>'neq','value'=>'1800-01-01']]]]),
            self::f('Error508',19,['all'=>[['age_years'=>true,'op'=>'lt','value'=>'12'],['field'=>19,'op'=>'neq','value'=>'98']]]),
            self::f('Error512',64,['all'=>[['field'=>22,'op'=>'eq','value'=>'21'],['field'=>64,'op'=>'not_in','values'=>$w]]]),
            self::f('Error513',22,['all'=>[['field'=>10,'op'=>'eq','value'=>'F'],['any'=>[['field'=>22,'op'=>'neq','value'=>'0'],['field'=>64,'op'=>'neq','value'=>'1845-01-01']]]]),
            self::f('Error516',24,['all'=>[['field'=>67,'op'=>'gt','value'=>'1900-01-01'],['field'=>24,'op'=>'not_in','values'=>['4','5','6']]]]),
            self::f('Error517',24,['all'=>[['field'=>67,'op'=>'in','values'=>array_slice($w,0,6)],['any'=>[['age_years'=>true,'op'=>'lt','value'=>'50'],['age_years'=>true,'op'=>'gt','value'=>'76'],['field'=>24,'op'=>'neq','value'=>'21']]]]),
            self::f('Error518',24,['all'=>[['field'=>67,'op'=>'eq','value'=>'1845-01-01'],['any'=>[['field'=>24,'op'=>'neq','value'=>'0'],['all'=>[['age_years'=>true,'op'=>'gte','value'=>'50'],['age_years'=>true,'op'=>'lte','value'=>'75']]]]]]),
            self::f('Error524',62,['all'=>[['field'=>27,'op'=>'in','values'=>['3','4','5','6','7','8','9']],['field'=>62,'op'=>'lte','value'=>'1900-01-01']]]),
            self::f('Error525',27,['all'=>[['field'=>62,'op'=>'in','values'=>array_slice($w,0,6)],['field'=>27,'op'=>'neq','value'=>'21']]]),
            self::f('Error526',62,['all'=>[['age_years'=>true,'op'=>'lt','value'=>'3'],['any'=>[['field'=>27,'op'=>'neq','value'=>'0'],['field'=>62,'op'=>'neq','value'=>'1845-01-01']]]]),
            self::f('Error528',62,['all'=>[['field'=>28,'op'=>'in','values'=>['3','4','5','6','7','8','9']],['field'=>62,'op'=>'lte','value'=>'1900-01-01']]]),
            self::f('Error529',28,['all'=>[['field'=>62,'op'=>'in','values'=>array_slice($w,0,6)],['field'=>28,'op'=>'neq','value'=>'21']]]),
            self::f('Error530',62,['all'=>[['age_years'=>true,'op'=>'lt','value'=>'3'],['any'=>[['field'=>28,'op'=>'neq','value'=>'0'],['field'=>62,'op'=>'neq','value'=>'1845-01-01']]]]),
            self::f('Error532',34,['all'=>[['field'=>34,'op'=>'eq','value'=>'170'],['field'=>3,'op'=>'in','values'=>['CE','PA','CD','PE','SC','DE']]]]),
            self::f('Error534',35,['all'=>[['field'=>35,'op'=>'in','values'=>['4','5']],['field'=>56,'op'=>'lt','value'=>'1900-01-01'],['field'=>58,'op'=>'lt','value'=>'1900-01-01']]]),
            self::f('Error542',69,['all'=>[['field'=>37,'op'=>'eq','value'=>'21'],['field'=>69,'op'=>'not_in','values'=>array_slice($w,0,6)]]]),
            self::f('Error543',75,['all'=>[['field'=>38,'op'=>'in','values'=>['4','5']],['field'=>75,'op'=>'lte','value'=>'1900-01-01']]]),
            self::f('Error546',75,['all'=>[['field'=>38,'op'=>'eq','value'=>'21'],['field'=>75,'op'=>'not_in','values'=>array_slice($w,0,6)]]]),
            self::f('Error548',63,['all'=>[['field'=>40,'op'=>'in','values'=>['4','5']],['any'=>[['age_years'=>true,'op'=>'gte','value'=>'13'],['field'=>63,'op'=>'lte','value'=>'1900-01-01']]]]),
            self::f('Error550',63,['all'=>[['field'=>40,'op'=>'eq','value'=>'0'],['any'=>[['age_years'=>true,'op'=>'lt','value'=>'13'],['field'=>63,'op'=>'neq','value'=>'1845-01-01']]]]),
            self::f('Error551',63,['all'=>[['field'=>40,'op'=>'eq','value'=>'21'],['any'=>[['age_years'=>true,'op'=>'gte','value'=>'13'],['field'=>63,'op'=>'not_in','values'=>array_slice($w,0,6)]]]]]),
            self::f('Error553',42,['all'=>[['field'=>110,'op'=>'gt','value'=>'1900-01-01'],['field'=>42,'op'=>'in','values'=>['0','21']]]]),
            self::f('Error554',42,['all'=>[['field'=>110,'op'=>'in','values'=>array_slice($w,0,6)],['field'=>42,'op'=>'neq','value'=>'21']]]),
            self::f('Warning555',42,['all'=>[['field'=>9,'op'=>'gt','value'=>'1996-12-31'],['any'=>[['field'=>42,'op'=>'neq','value'=>'0'],['field'=>110,'op'=>'neq','value'=>'1845-01-01']]]],'WARNING'),
            self::f('Error556',42,['all'=>[['field'=>110,'op'=>'eq','value'=>'1845-01-01'],['field'=>42,'op'=>'neq','value'=>'0']]]),
            self::f('Error558',52,['all'=>[['field'=>43,'op'=>'in','values'=>['3','4','5']],['field'=>52,'op'=>'lte','value'=>'1900-01-01']]]),
            self::f('Error560',43,['any'=>[
                ['all'=>[['field'=>43,'op'=>'in','values'=>['3','4','5','21']],['age_years'=>true,'op'=>'gte','value'=>'8']]],
                ['all'=>[['field'=>43,'op'=>'eq','value'=>'0'],['age_years'=>true,'op'=>'lt','value'=>'8']]],
            ]]),
            self::f('Warning561',43,['all'=>[['field'=>52,'op'=>'not_in','values'=>array_slice($w,0,6)],['field'=>43,'op'=>'eq','value'=>'21'],['age_years'=>true,'op'=>'lt','value'=>'8']]],'WARNING'),
            self::f('Error562',52,['all'=>[['field'=>44,'op'=>'in','values'=>['3','4','5']],['field'=>52,'op'=>'lte','value'=>'1900-01-01']]]),
            self::f('Error564',44,['any'=>[
                ['all'=>[['field'=>44,'op'=>'in','values'=>['3','4','5','21']],['age_years'=>true,'op'=>'gte','value'=>'8']]],
                ['all'=>[['field'=>44,'op'=>'eq','value'=>'0'],['age_years'=>true,'op'=>'lt','value'=>'8']]],
            ]]),
            self::f('Warning565',44,['all'=>[['field'=>52,'op'=>'not_in','values'=>array_slice($w,0,6)],['field'=>44,'op'=>'eq','value'=>'21'],['age_years'=>true,'op'=>'lt','value'=>'8']]],'WARNING'),
            self::f('Error566',52,['all'=>[['field'=>45,'op'=>'in','values'=>['3','4','5']],['field'=>52,'op'=>'lte','value'=>'1900-01-01']]]),
            self::f('Error568',45,['any'=>[
                ['all'=>[['field'=>45,'op'=>'in','values'=>['3','4','5','21']],['age_years'=>true,'op'=>'gte','value'=>'8']]],
                ['all'=>[['field'=>45,'op'=>'eq','value'=>'0'],['age_years'=>true,'op'=>'lt','value'=>'8']]],
            ]]),
            self::f('Warning569',45,['all'=>[['field'=>52,'op'=>'not_in','values'=>array_slice($w,0,6)],['field'=>45,'op'=>'eq','value'=>'21'],['age_years'=>true,'op'=>'lt','value'=>'8']]],'WARNING'),
            self::f('Error570',52,['all'=>[['field'=>46,'op'=>'in','values'=>['3','4','5']],['field'=>52,'op'=>'lte','value'=>'1900-01-01']]]),
            self::f('Warning573',46,['all'=>[['field'=>52,'op'=>'not_in','values'=>array_slice($w,0,6)],['field'=>46,'op'=>'eq','value'=>'21'],['age_years'=>true,'op'=>'lt','value'=>'8']]],'WARNING'),
            self::f('Error575',47,['all'=>[['field'=>88,'op'=>'neq','value'=>'19'],['field'=>47,'op'=>'neq','value'=>'0']]]),
            self::f('Error578',65,['all'=>[['field'=>48,'op'=>'in','values'=>['4','5']],['field'=>65,'op'=>'lte','value'=>'1900-01-01']]]),
            self::f('Warning580',48,['all'=>[['field'=>65,'op'=>'gt','value'=>'1900-01-01'],['age_days'=>true,'op'=>'gt','value'=>'30'],['field'=>48,'op'=>'in','values'=>['4','5']]],'WARNING'),
            self::f('Error581',65,['all'=>[['field'=>48,'op'=>'eq','value'=>'21'],['field'=>65,'op'=>'not_in','values'=>array_slice($w,0,6)]]]),
            self::f('Error583',51,['all'=>[['field'=>14,'op'=>'neq','value'=>'1'],['age_months'=>true,'op'=>'gte','value'=>'7'],['field'=>51,'op'=>'neq','value'=>'1845-01-01']]]),
            self::f('Error584',57,['all'=>[['field'=>105,'op'=>'gt','value'=>'1900-01-01'],['any'=>[['field'=>57,'op'=>'eq','value'=>'0'],['field'=>57,'op'=>'gte','value'=>'998']]]]),
            self::f('Error585',57,['any'=>[
                ['all'=>[['field'=>105,'op'=>'in','values'=>array_slice($w,0,6)],['field'=>57,'op'=>'neq','value'=>'998']]],
                ['all'=>[['field'=>57,'op'=>'eq','value'=>'998'],['field'=>105,'op'=>'not_in','values'=>array_slice($w,0,6)]]],
            ]]),
            self::f('Error586',57,['all'=>[['field'=>105,'op'=>'eq','value'=>'1845-01-01'],['any'=>[['field'=>57,'op'=>'neq','value'=>'0'],['age_years'=>true,'op'=>'gte','value'=>'29']]]]),
            self::f('Error590',62,['all'=>[['field'=>27,'op'=>'eq','value'=>'0'],['field'=>28,'op'=>'eq','value'=>'0'],['field'=>62,'op'=>'neq','value'=>'1845-01-01']]]),
            self::f('Error597',76,['any'=>[
                ['all'=>[['field'=>102,'op'=>'eq','value'=>'0'],['field'=>76,'op'=>'neq','value'=>'1845-01-01']]],
                ['all'=>[['field'=>102,'op'=>'neq','value'=>'0'],['field'=>76,'op'=>'eq','value'=>'1845-01-01']]],
            ]]),
            self::f('Error599',79,['all'=>[['field'=>78,'op'=>'in','values'=>array_slice($w,0,6)],['field'=>79,'op'=>'neq','value'=>'21']]]),
        ];
    }

    private static function f(string $code,int $variable,array $when,string $severity='ERROR'): array
    {
        return ['code'=>$code,'severity'=>$severity,'operation'=>'forbidden_when','variable'=>$variable,'when'=>$when,'message'=>'Validación RPED v8 no cumplida: '.$code];
    }

    private static function wildcards(): array
    {
        return ['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'];
    }
}
