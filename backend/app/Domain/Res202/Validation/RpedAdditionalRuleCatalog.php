<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

/** Reglas RPED adicionales implementadas después del catálogo base. */
final class RpedAdditionalRuleCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function executable(): array
    {
        return [
            self::regex('Error220', 4, '/^[A-Z0-9]+$/', 'Número de identificación con caracteres no permitidos'),
            self::forbidden('Error222', 'Si la edad es menor de 10 años o mayor o igual de 60 años, la Gestación debe ser No aplica', ['all' => [['field'=>14,'op'=>'neq','value'=>'0'], ['any'=>[['age_months'=>true,'op'=>'lt','value'=>120],['age_months'=>true,'op'=>'gte','value'=>720]]]]], 14),
            self::forbidden('Error223', 'Si el sexo es Femenino y la edad está entre 10 años y menor de 60 años, Gestante no debe ser No aplica', ['all' => [['field'=>10,'op'=>'eq','value'=>'F'], ['age_months'=>true,'op'=>'gte','value'=>120], ['age_months'=>true,'op'=>'lt','value'=>720], ['field'=>14,'op'=>'eq','value'=>'0']]], 14),
            self::forbidden('Error227', 'El resultado de la prueba mini-mental state no es acorde al rango de edad', ['any' => [
                ['all'=>[['field'=>16,'op'=>'in','values'=>['4','5','21']],['age_months'=>true,'op'=>'lt','value'=>720]]],
                ['all'=>[['field'=>16,'op'=>'eq','value'=>'0'],['age_months'=>true,'op'=>'gte','value'=>720]]],
            ]], 16),
            self::forbidden('Error232', 'Si es sintomático respiratorio, el resultado de baciloscopia diagnóstica debe ser diferente de 4 y la fecha debe ser válida o comodín de no realización', ['all'=>[['field'=>18,'op'=>'eq','value'=>'1'],['any'=>[['field'=>113,'op'=>'eq','value'=>'4'],['field'=>112,'op'=>'eq','value'=>'1845-01-01']]]]], 18),
            self::forbidden('Error237', 'Si registra fecha del tacto rectal, debe registrar resultado válido, sexo M y edad mayor o igual a 40 años', ['all'=>[['field'=>64,'op'=>'gt','value'=>'1900-01-01'],['any'=>[['field'=>22,'op'=>'not_in','values'=>['4','5']],['field'=>10,'op'=>'neq','value'=>'M'],['age_months'=>true,'op'=>'lt','value'=>480]]]]], 64),
            ['code'=>'Error242','severity'=>'ERROR','operation'=>'date_after_cutoff_plus_days','variable'=>33,'days'=>280,'message'=>'Si registra fecha probable de parto no debe ser mayor a la fecha de corte más 280 días'],
            ['code'=>'Error243','severity'=>'ERROR','operation'=>'date_relation','variable'=>33,'other_variable'=>56,'relation'=>'lte','min_valid_date'=>'1900-01-01','message'=>'Fecha probable de parto es menor o igual a fecha de primera consulta prenatal'],
            self::forbidden('Error244', 'Si no es gestante, las variables relacionadas con la gestación deben registrar No aplica', ['all'=>[
                ['field'=>14,'op'=>'neq','value'=>'1'],
                ['any'=>[
                    ['field'=>23,'op'=>'neq','value'=>'0'],['field'=>35,'op'=>'neq','value'=>'0'],['field'=>59,'op'=>'neq','value'=>'0'],['field'=>60,'op'=>'neq','value'=>'0'],['field'=>61,'op'=>'neq','value'=>'0'],
                    ['field'=>33,'op'=>'neq','value'=>'1845-01-01'],['field'=>56,'op'=>'neq','value'=>'1845-01-01'],['field'=>58,'op'=>'neq','value'=>'1845-01-01'],
                ]],
            ]], 14),
        ];
    }

    private static function regex(string $code, int $variable, string $pattern, string $message): array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'regex','variable'=>$variable,'pattern'=>$pattern,'message'=>$message];
    }

    private static function forbidden(string $code, string $message, array $when, int $variable): array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'forbidden_when','variable'=>$variable,'when'=>$when,'message'=>$message];
    }
}
