<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

final class RpedEarlierClarifiedRuleCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function executable(): array
    {
        return [
            ['code'=>'Error079','severity'=>'ERROR','operation'=>'forbidden_when','variable'=>10,'when'=>['all'=>[
                ['field'=>91,'op'=>'neq','value'=>'1845-01-01'],
                ['field'=>10,'op'=>'neq','value'=>'F'],
            ]],'message'=>'Si registra Fecha de colposcopia, el sexo debe ser F.'],
            ['code'=>'Error153','severity'=>'ERROR','operation'=>'date_after_cutoff','variable'=>103,'message'=>'La fecha de la variable 103 es mayor a la fecha de corte.'],
            ['code'=>'Error402','severity'=>'ERROR','operation'=>'forbidden_when','variable'=>76,'when'=>['all'=>[
                ['age_months'=>true,'op'=>'gte','value'=>'6'],
                ['field'=>76,'op'=>'eq','value'=>'1845-01-01'],
            ]],'message'=>'Si registra Fecha atención en salud bucal por profesional en odontología, la edad debe ser mayor o igual a 6 meses.'],
        ];
    }
}
