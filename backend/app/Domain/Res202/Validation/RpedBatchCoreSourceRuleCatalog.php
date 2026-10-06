<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

/**
 * Reglas RPED v8 traducidas directamente de Lineamientos RPED.
 * Este lote contiene únicamente validaciones cuya condición está explícita
 * en la fuente y puede representarse sin catálogos externos.
 */
final class RpedBatchCoreSourceRuleCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function executable(): array
    {
        return [
            // Contenido de fecha.
            ...array_map(static fn(array $r): array => self::dateValue($r[0], $r[1]), [
                ['Error421',9],['Error422',29],['Error423',31],['Error424',33],['Error425',49],
                ['Error426',50],['Error427',51],['Error447',52],['Error448',53],['Error428',55],
                ['Error449',56],['Error450',58],['Error451',62],['Error452',63],['Error453',64],
                ['Error454',65],['Error455',66],['Error456',67],['Error457',68],['Error458',69],
                ['Error459',72],['Error460',73],['Error461',75],['Error462',76],['Error429',78],
                ['Error430',80],['Error431',82],['Error432',84],['Error434',91],['Error435',93],
                ['Error436',96],['Error437',99],['Error438',100],['Error439',103],['Error440',105],
                ['Error441',106],['Error442',108],['Error443',110],['Error444',111],['Error445',112],
                ['Error446',118],
            ]),

            // Fechas posteriores a la fecha de corte.
            ...array_map(static fn(array $r): array => self::afterCutoff($r[0], $r[1]), [
                ['Error132',63],['Error133',64],['Error134',65],['Error135',66],['Error136',67],
                ['Error138',69],['Error140',73],['Error141',75],['Error142',76],['Error143',78],
            ]),

            // Comodines: los conjuntos se toman de la definición de cada variable en el anexo.
            self::wildcards('Error381',29,['1800-01-01']),
            self::wildcards('Error382',31,['1800-01-01']),
            self::wildcards('Error380',33,['1800-01-01','1845-01-01']),
            self::wildcards('Error383',49,['1800-01-01','1845-01-01']),
            self::wildcards('Error384',50,['1800-01-01','1845-01-01']),
            self::wildcards('Error385',51,self::allWildcards()),
            self::wildcards('Error386',52,self::allWildcards()),
            self::wildcards('Error387',53,self::allWildcards()),
            self::wildcards('Error388',55,self::allWildcards()),
            self::wildcards('Error389',56,self::allWildcards()),
            self::wildcards('Error390',58,['1800-01-01','1845-01-01']),
            self::wildcards('Error391',62,self::allWildcards()),
            self::wildcards('Error392',63,self::allWildcards()),
            self::wildcards('Error393',64,self::allWildcards()),
            self::wildcards('Error394',65,self::allWildcards()),
            self::wildcards('Error395',66,self::allWildcards()),
            self::wildcards('Error396',67,self::allWildcards()),
            self::wildcards('Error398',69,self::allWildcards()),
            self::wildcards('Error399',72,self::allWildcards()),
            self::wildcards('Error400',73,self::allWildcards()),
            self::wildcards('Error401',75,self::allWildcards()),
            self::wildcards('Error672',76,self::allWildcards()),
            self::wildcards('Error403',78,self::allWildcards()),
            self::wildcards('Error404',80,self::allWildcards()),
            self::wildcards('Error405',82,self::allWildcards()),
            self::wildcards('Error406',84,self::allWildcards()),
            self::wildcards('Error407',87,self::allWildcards()),
            self::wildcards('Error408',91,self::allWildcards()),
            self::wildcards('Error409',93,self::allWildcards()),
            self::wildcards('Error410',96,self::allWildcards()),
            self::wildcards('Error411',99,self::allWildcards()),
            self::wildcards('Error412',100,['1845-01-01','1800-01-01']),
            self::wildcards('Error413',103,self::allWildcards()),
            self::wildcards('Error644',105,self::allWildcards()),
            self::wildcards('Error415',106,self::allWildcards()),
            self::wildcards('Error417',110,self::allWildcards()),
            self::wildcards('Error418',111,self::allWildcards()),
            self::wildcards('Error419',112,self::allWildcards()),
            self::wildcards('Error668',118,self::allWildcards()),

            // Warnings explícitos sobre comodín/valores físicos.
            self::equalsValue('Warning674',29,'1800-01-01','Verifique el comodín 1800-01-01, registre una fecha válida.','WARNING'),
            self::equalsValue('Warning675',31,'1800-01-01','Verifique el comodín 1800-01-01, registre una fecha válida.','WARNING'),
            self::warningRange('Warning040',30,'999',0.2,250,'El peso de la persona debe ser mayor a 0.2 kg y menor o igual a 250 kg.'),
            self::warningRange('Warning042',32,'999',20,225,'La talla de la persona debe ser mayor a 20 cm y menor o igual a 225 cm.'),

            // Valores permitidos explícitos.
            self::inValues('Error500',15,['0'],'Error en valores permitidos - Sífilis Gestacional o congénita'),
            self::inValues('Error504',16,['0','4','5','21'],'Error en valores permitidos - Resultado del test minimental state'),
            self::inValues('Error505',17,['0'],'Error en valores permitidos - Hipotiroidismo Congénito'),
            self::inValues('Error509',20,['21'],'Error en valores permitidos - Lepra'),
            self::inValues('Error510',21,['21'],'Error en valores permitidos - Obesidad o Desnutrición Proteico Calórica'),
            self::inValues('Error514',22,['0','4','5','21'],'Error en valores permitidos - Resultado tacto rectal'),
            self::inValues('Error515',23,['0','1','2','21'],'Error en valores permitidos - Ácido fólico preconcepcional'),
            self::inValues('Error519',24,['0','4','5','6','21'],'Error en valores permitidos - Resultado test de sangre oculta en materia fecal'),
            self::inValues('Error520',25,['21'],'Error en valores permitidos - Enfermedad mental'),
            self::inValues('Error521',26,['0'],'Error en valores permitidos - Cáncer de Cérvix'),
            self::inValues('Error527',27,['0','3','4','5','6','7','8','9','21'],'Error en valores permitidos - Agudeza visual lejana ojo izquierdo'),
            self::inValues('Error531',28,['0','3','4','5','6','7','8','9','21'],'Error en valores permitidos - Agudeza visual lejana ojo derecho'),
            self::inValues('Error533',35,['0','4','5','21'],'Error en valores permitidos - Clasificación del riesgo gestacional'),
            self::inValues('Error538',36,['0','2','3','4','5','6','21'],'Error en valores permitidos - Resultado colonoscopia de tamizaje'),
            self::inValues('Error540',37,['0','4','5','21'],'Error en valores permitidos - Resultado de tamizaje auditivo neonatal'),
            self::inValues('Error544',38,['0','4','5','21'],'Error en valores permitidos - Resultado de tamizaje visual neonatal'),
            self::inValues('Error549',40,['0','4','5','21'],'Error en valores permitidos - Resultado de tamizaje VALE'),
            self::inValues('Error552',41,['0'],'Error en valores permitidos - Neumococo'),
            self::inValues('Error557',42,['0','4','5','21'],'Error en valores permitidos - Resultado de tamizaje para hepatitis C'),
            self::inValues('Error559',43,['0','3','4','5','21'],'Error en valores permitidos - Resultado escala abreviada de desarrollo área de motricidad gruesa'),
            self::inValues('Error563',44,['0','3','4','5','21'],'Error en valores permitidos - Resultado escala abreviada de desarrollo área de motricidad finoadaptativa'),
            self::inValues('Error567',45,['0','3','4','5','21'],'Error en valores permitidos - Resultado escala abreviada de desarrollo área personal social'),
            self::inValues('Error571',46,['0','3','4','5','21'],'Error en valores permitidos - Resultado escala abreviada de desarrollo área de motricidad audición lenguaje'),
            self::inValues('Error576',47,['0','6','7','8','9','10','21'],'Error en valores permitidos - Tratamiento ablativo o de escisión'),
            self::inValues('Error579',48,['0','4','5','21'],'Error en valores permitidos - Resultado de tamización con oximetría pre y post ductal'),
            self::inValues('Error574',39,['0'],'Error en valores permitidos - DPT menores de 5 años'),
            self::inValues('Error587',59,['0','1','16','17','18','20','21'],'Error en valores permitidos - Suministro de ácido fólico en el control prenatal'),
            self::inValues('Error588',60,['0','1','16','17','18','20','21'],'Error en valores permitidos - Suministro de sulfato ferroso en el control prenatal'),
            self::inValues('Error589',61,['0','1','16','17','18','20','21'],'Error en valores permitidos - Suministro de carbonato de calcio en el control prenatal'),
            self::inValues('Error591',68,['1845-01-01'],'Error en valores permitidos - Consulta de Psicología'),
            self::inValues('Error592',70,['0','1','16','17','18','20','21'],'Error en valores permitidos - Suministro de fortificación casera en la primera infancia'),
            self::inValues('Error593',71,['0','1','16','17','18','20','21'],'Error en valores permitidos - Suministro de vitamina A en la primera infancia'),
            self::inValues('Error594',74,['0'],'Error en valores permitidos - Preservativos entregados a pacientes con ITS'),
            self::inValues('Error598',77,['0','1','16','17','18','20','21'],'Error en valores permitidos - Suministro de hierro en la primera infancia'),
        ];
    }

    private static function dateValue(string $code,int $variable): array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'date_value_valid','variable'=>$variable,'allowed_wildcards'=>self::allWildcards(),'message'=>'Contenido de fecha no válido en la variable '.$variable];
    }

    private static function afterCutoff(string $code,int $variable): array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'date_after_cutoff','variable'=>$variable,'message'=>'La fecha de la variable '.$variable.' es mayor a la fecha de corte'];
    }

    private static function wildcards(string $code,int $variable,array $allowed): array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'wildcard_allowed','variable'=>$variable,'allowed_wildcards'=>$allowed,'message'=>'Comodín inválido en la variable '.$variable];
    }

    private static function equalsValue(string $code,int $variable,string $value,string $message,string $severity): array
    {
        return ['code'=>$code,'severity'=>$severity,'operation'=>'value_equals','variable'=>$variable,'expected'=>$value,'message'=>$message];
    }

    private static function inValues(string $code,int $variable,array $values,string $message): array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'in','variable'=>$variable,'values'=>$values,'message'=>$message];
    }

    private static function warningRange(string $code,int $variable,string $excluded,float $min,float $max,string $message): array
    {
        return ['code'=>$code,'severity'=>'WARNING','operation'=>'forbidden_when','variable'=>$variable,'when'=>['all'=>[
            ['field'=>$variable,'op'=>'neq','value'=>$excluded],
            ['any'=>[
                ['field'=>$variable,'op'=>'lte','value'=>(string)$min],
                ['field'=>$variable,'op'=>'gt','value'=>(string)$max],
            ]],
        ]],'message'=>$message];
    }

    /** @return array<int,string> */
    private static function allWildcards(): array
    {
        return ['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'];
    }
}
