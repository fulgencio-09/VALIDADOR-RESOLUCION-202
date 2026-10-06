<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

/** Reglas RPED Error294-Error399 implementadas desde Lineamientos v8. */
final class RpedBatch294RuleCatalog
{
    /** @return array<int,array<string,mixed>> */
    public static function executable(): array
    {
        return [
            self::forbidden('Error294','Si registra Fecha de atención parto ó cesárea, debe ser mayor o igual de 10 años y menor de 60 años',['all'=>[['field'=>49,'op'=>'neq','value'=>'1845-01-01'],['any'=>[['age_months'=>true,'op'=>'lt','value'=>120],['age_months'=>true,'op'=>'gte','value'=>720]]]]],49),
            self::forbidden('Error296','Si registra fecha de atención parto ó cesárea, debe registrar fecha de salida de atención parto o cesárea',['all'=>[['field'=>49,'op'=>'neq','value'=>'1845-01-01'],['field'=>50,'op'=>'eq','value'=>'1845-01-01']]],50),
            self::forbidden('Error299','Si registra Fecha de salida de atención parto ó cesárea, debe ser mayor o igual de 10 años y menor de 60 años',['all'=>[['field'=>50,'op'=>'neq','value'=>'1845-01-01'],['any'=>[['age_months'=>true,'op'=>'lt','value'=>120],['age_months'=>true,'op'=>'gte','value'=>720]]]]],50),
            self::forbidden('Error300','Fecha de salida de parto es menor a fecha de atención del parto',['all'=>[['field'=>49,'op'=>'gt','value'=>'1900-01-01'],['field'=>50,'op'=>'gt','value'=>'1900-01-01'],['field'=>49,'op'=>'gt_field','value'=>50]]],50),
            self::forbidden('Error301','Si registra Fecha de salida de atención parto o cesárea, debe registrar Fecha de atención parto o cesárea',['all'=>[['field'=>50,'op'=>'not_in','values'=>['1845-01-01','1800-01-01']],['field'=>49,'op'=>'in','values'=>['1845-01-01','1800-01-01']]]],49),
            self::forbidden('Error304','Verifique la actividad Fecha de Atención en salud para la asesoría en anticoncepción reportada en población menor de 10 años',['all'=>[['field'=>53,'op'=>'neq','value'=>'1845-01-01'],['age_months'=>true,'op'=>'lt','value'=>120]]],53),
            self::forbidden('Error305','Verifique la Fecha de Atención en salud para la asesoría en anticoncepción con reporte no aplica en población mayor o igual de 10 años',['all'=>[['field'=>53,'op'=>'eq','value'=>'1845-01-01'],['age_months'=>true,'op'=>'gte','value'=>120]]],53),
            self::forbidden('Error306','Si registró no aplica en la fecha de suministro de método, debe registrar no aplica en el método anticonceptivo suministrado',['all'=>[['field'=>55,'op'=>'eq','value'=>'1845-01-01'],['field'=>54,'op'=>'neq','value'=>'0']]],54),
            self::forbidden('Warning307','Si registra suministro de método, debe ser mayor o igual de 10 años y menor de 60 años',['all'=>[['field'=>54,'op'=>'neq','value'=>'0'],['field'=>55,'op'=>'gt','value'=>'1900-01-01'],['any'=>[['age_months'=>true,'op'=>'lt','value'=>120],['age_months'=>true,'op'=>'gte','value'=>720]]]]],54,'WARNING'),
            self::forbidden('Error308','No es válido registrar no aplica en suministro de método anticonceptivo si la edad está entre 10 y 59 años',['all'=>[['field'=>54,'op'=>'eq','value'=>'0'],['age_months'=>true,'op'=>'gte','value'=>120],['age_months'=>true,'op'=>'lt','value'=>720]]],54),
            self::forbidden('Error309','Si registra fecha de suministro de método anticonceptivo, debe registrar un dato diferente a 0 en el suministro',['all'=>[['field'=>55,'op'=>'neq','value'=>'1845-01-01'],['field'=>54,'op'=>'eq','value'=>'0']]],54),
            self::forbidden('Error318','Fecha de ultimo control prenatal de seguimiento es menor a Fecha de primera consulta prenatal',['all'=>[['field'=>56,'op'=>'gt','value'=>'1900-01-01'],['field'=>58,'op'=>'gt','value'=>'1900-01-01'],['field'=>56,'op'=>'gt_field','value'=>58]]],58),
            self::forbidden('Error328','No es válido registrar No aplica en Suministro de fortificación casera para la edad reportada',['all'=>[['field'=>70,'op'=>'eq','value'=>'0'],['age_months'=>true,'op'=>'gte','value'=>6],['age_months'=>true,'op'=>'lte','value'=>27]]],70),
            self::forbidden('Error329','No es válido registrar No aplica en Suministro de Vitamina A en la primera Infancia para la edad reportada',['all'=>[['field'=>71,'op'=>'eq','value'=>'0'],['age_months'=>true,'op'=>'gte','value'=>24],['age_months'=>true,'op'=>'lte','value'=>63]]],71),
            self::forbidden('Error341','Si registra fecha válida para Fecha antígeno de superficie hepatitis B, debe registrar resultado',['all'=>[['field'=>78,'op'=>'gt','value'=>'1900-01-01'],['field'=>79,'op'=>'in','values'=>['0','21']]]],79),
            self::forbidden('Error344','Si registra Fecha de toma de la prueba de tamizaje para sífilis, debe registrar resultado de prueba',['all'=>[['field'=>80,'op'=>'gt','value'=>'1900-01-01'],['field'=>81,'op'=>'in','values'=>['0','21']]]],81),
            self::forbidden('Error346','Si registra fecha de toma de prueba para VIH, debe registrar resultado para prueba de VIH',['all'=>[['field'=>82,'op'=>'gt','value'=>'1900-01-01'],['field'=>83,'op'=>'in','values'=>['0','21']]]],83),
            self::forbidden('Error350','Si registra fecha de toma de TSH Neonatal, debe registrar resultado de TSH Neonatal',['all'=>[['field'=>84,'op'=>'neq','value'=>'1845-01-01'],['field'=>85,'op'=>'eq','value'=>'0']]],85),
            self::forbidden('Error352','Si registra Fecha de tamizaje cáncer de cuello uterino, debe registrar Resultado tamizaje de cáncer de cuello uterino',['all'=>[['field'=>87,'op'=>'gt','value'=>'1900-01-01'],['any'=>[['field'=>88,'op'=>'lt','value'=>'1'],['field'=>88,'op'=>'gt','value'=>'20']]]]],88),
            self::forbidden('Error354','Si registra calidad de la muestra, debe registrar resultado de citología',['all'=>[['field'=>89,'op'=>'in','values'=>['1','2','3']],['any'=>[['field'=>88,'op'=>'lt','value'=>'1'],['field'=>88,'op'=>'gt','value'=>'18']]]]],88),
            self::forbidden('Error355','Si registra calidad de la muestra, debe registrar código de habilitación IPS',['all'=>[['field'=>89,'op'=>'in','values'=>['1','2','3','4']],['field'=>90,'op'=>'in','values'=>['0','999']]]],90),
            self::forbidden('Error359','Si registra resultado de biopsia cervicouterina, debe registrar Fecha biopsia cervicouterina',['all'=>[['field'=>94,'op'=>'in','values'=>['1','3','4','5','6']],['field'=>93,'op'=>'in','values'=>['1845-01-01','1800-01-01']]]],93),
            self::forbidden('Error361','Si registra fecha de mamografía, debe registrar resultado de mamografía',['all'=>[['field'=>96,'op'=>'gt','value'=>'1900-01-01'],['field'=>97,'op'=>'not_in','values'=>['1','2','3','4','5','6','7']]]],97),
            self::forbidden('Error362','Si es mujer mayor o igual de 50 años, no es válido registrar No aplica para mamografía',['all'=>[['field'=>10,'op'=>'eq','value'=>'F'],['field'=>96,'op'=>'eq','value'=>'1845-01-01'],['age_months'=>true,'op'=>'gte','value'=>600]]],96),
            self::forbidden('Error364','Si es menor de 35 años no aplica para resultado de mamografía',['all'=>[['age_months'=>true,'op'=>'lt','value'=>420],['field'=>97,'op'=>'neq','value'=>'0']]],97),
            self::forbidden('Error367','Si registra sin dato o fecha válida de resultado de biopsia de mama, debe registrar sin dato o el resultado de biopsia de mama',['all'=>[['any'=>[['field'=>100,'op'=>'eq','value'=>'1800-01-01'],['field'=>100,'op'=>'gt','value'=>'1900-01-01']]],['field'=>101,'op'=>'eq','value'=>'0']]],101),
            self::forbidden('Error368','La Fecha de resultado de biopsia de mama es menor a la fecha de toma de la biopsia',['all'=>[['field'=>99,'op'=>'gt','value'=>'1900-01-01'],['field'=>100,'op'=>'gt','value'=>'1900-01-01'],['field'=>100,'op'=>'lte_field','value'=>99]]],100),
            self::forbidden('Error369','Si registra un Resultado de biopsia de mama debe registrar Fecha resultado de biopsia de mama',['all'=>[['field'=>101,'op'=>'in','values'=>['1','2','3','4','5']],['field'=>100,'op'=>'lte','value'=>'1900-01-01']]],100),
            self::forbidden('Error371','Si registra Fecha toma creatinina, debe registrar el Resultado de creatinina',['all'=>[['field'=>106,'op'=>'gt','value'=>'1900-01-01'],['any'=>[['field'=>107,'op'=>'lte','value'=>'0'],['field'=>107,'op'=>'gte','value'=>'998']]]]],107),
            self::forbidden('Error375','Si registra fecha de toma de baciloscopia, debe registrar Resultado de baciloscopia diagnóstico',['all'=>[['field'=>112,'op'=>'gt','value'=>'1900-01-01'],['field'=>113,'op'=>'not_in','values'=>['1','2','3']]]]],113),
            self::forbidden('Error379','Si es gestante, las variables relacionadas con la gestación deben registrar un dato diferente a No aplica',['all'=>[['field'=>14,'op'=>'eq','value'=>'1'],['any'=>[['field'=>23,'op'=>'eq','value'=>'0'],['field'=>35,'op'=>'eq','value'=>'0'],['field'=>59,'op'=>'eq','value'=>'0'],['field'=>60,'op'=>'eq','value'=>'0'],['field'=>61,'op'=>'eq','value'=>'0'],['field'=>33,'op'=>'eq','value'=>'1845-01-01'],['field'=>56,'op'=>'eq','value'=>'1845-01-01'],['field'=>58,'op'=>'eq','value'=>'1845-01-01']]]],14),
            self::wildcard('Error380',33,['1800-01-01','1845-01-01'],'Comodín inválido - Fecha probable parto'),
            self::wildcard('Error381',29,['1800-01-01'],'No se permite el registro de estos comodines en Fecha peso'),
            self::wildcard('Error382',31,['1800-01-01'],'No se permite el registro de estos comodines en Fecha de la talla'),
            self::wildcard('Error383',49,['1800-01-01','1845-01-01'],'Comodín inválido - Fecha atención parto o cesárea'),
            self::wildcard('Error384',50,['1800-01-01','1845-01-01'],'Comodín inválido - Fecha salida del parto o cesárea'),
            self::wildcard('Error385',51,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha atención en salud para la promoción y apoyo de la lactancia materna'),
            self::wildcard('Error386',52,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha de consulta de valoración integral'),
            self::wildcard('Error387',53,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Atención en salud para la asesoría en anticoncepción'),
            self::wildcard('Error388',55,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha suministro método anticonceptivo'),
            self::wildcard('Error389',56,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha de Primera Consulta Prenatal'),
            self::wildcard('Error390',58,['1800-01-01','1845-01-01'],'Comodín inválido - Fecha de último control prenatal de seguimiento'),
            self::wildcard('Error391',62,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha de valoración de la agudeza visual'),
            self::wildcard('Error392',63,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha de tamizaje VALE'),
            self::wildcard('Error393',64,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha del tacto rectal'),
            self::wildcard('Error394',65,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha tamización con oximetría pre y post ductal'),
            self::wildcard('Error395',66,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha de realización colonoscopia tamizaje'),
            self::wildcard('Error396',67,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha de la prueba de sangre oculta en materia fecal'),
            self::wildcard('Error398',69,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha de tamizaje auditivo neonatal'),
            self::wildcard('Error399',72,['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'],'Comodín inválido - Fecha de toma LDL'),
        ];
    }

    private static function forbidden(string $code, string $message, array $when, int $variable, string $severity='ERROR'): array
    {
        return ['code'=>$code,'severity'=>$severity,'operation'=>'forbidden_when','variable'=>$variable,'when'=>$when,'message'=>$message];
    }

    private static function wildcard(string $code, int $variable, array $allowed, string $message): array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'wildcard_allowed','variable'=>$variable,'allowed_wildcards'=>$allowed,'message'=>$message];
    }
}
