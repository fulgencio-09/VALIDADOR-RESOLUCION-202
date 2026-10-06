<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

final class RpedRuleCatalog
{
    /**
     * Ejecutable solo cuando la operación está implementada y la regla fue
     * contrastada contra el Lineamientos RPED v8. Las reglas que dependen de
     * REPS o que presentan una ambigüedad textual se mantienen pendientes.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function executable(): array
    {
        return [
            ['code'=>'Error020','severity'=>'ERROR','operation'=>'required','variable'=>9,'message'=>'La fecha de nacimiento es requerida'],
            ['code'=>'Error030','severity'=>'ERROR','operation'=>'equals_when_any','variable'=>10,'when_variable'=>14,'when_value'=>'1|2|21','expected'=>'F','message'=>'Si registra 1, 2 ó 21 en la variable Gestante, el sexo debe ser F'],
            ['code'=>'Error041','severity'=>'ERROR','operation'=>'equals_when','variable'=>29,'when_variable'=>30,'when_value'=>'999','expected'=>'1800-01-01','message'=>'Si no registra el peso de la persona no debe registrar fecha de medición'],
            ['code'=>'Error043','severity'=>'ERROR','operation'=>'equals_when','variable'=>31,'when_variable'=>32,'when_value'=>'999','expected'=>'1800-01-01','message'=>'Si no registra la talla de la persona no debe registrar fecha de medición'],

            self::forbidden('Error037','Si registra 4, 5 o 21 en Resultado del tacto rectal, el sexo debe ser M',['all'=>[['field'=>22,'op'=>'in','values'=>['4','5','21']],['field'=>10,'op'=>'neq','value'=>'M']]],22),
            self::forbidden('Error038','Si es hombre menor de 40 años, debe registrar no aplica en resultado y fecha del tacto rectal',['all'=>[['age_months'=>true,'op'=>'lt','value'=>480],['field'=>10,'op'=>'eq','value'=>'M'],['any'=>[['field'=>22,'op'=>'neq','value'=>'0'],['field'=>64,'op'=>'neq','value'=>'1845-01-01']]]],22),
            self::forbidden('Error047','Si reporta tratamiento ablativo o de escisión, el sexo debe ser F',['all'=>[['field'=>47,'op'=>'neq','value'=>'0'],['field'=>10,'op'=>'neq','value'=>'F']]],47),
            self::forbidden('Error049','Si registra Fecha de atención parto o cesárea, el sexo debe ser F',['all'=>[['field'=>49,'op'=>'neq','value'=>'1845-01-01'],['field'=>10,'op'=>'neq','value'=>'F']]],49),
            self::forbidden('Error050','Si registra fecha de salida de atención de parto, el sexo debe ser F',['all'=>[['field'=>50,'op'=>'neq','value'=>'1845-01-01'],['field'=>10,'op'=>'neq','value'=>'F']]],50),
            self::forbidden('Error051','La atención de lactancia no aplica a personas de sexo M con edad mayor o igual a 7 meses',['all'=>[['field'=>51,'op'=>'neq','value'=>'1845-01-01'],['field'=>10,'op'=>'eq','value'=>'M'],['age_months'=>true,'op'=>'gte','value'=>7]]],51),

            self::forbidden('Error063','Verifique la edad de la persona con suministro de fortificación casera',['all'=>[['field'=>70,'op'=>'in','values'=>['1','16','17','18','20','21']],['any'=>[['age_months'=>true,'op'=>'lt','value'=>6],['age_months'=>true,'op'=>'gt','value'=>27]]]]],70),
            self::forbidden('Error064','Verifique la edad de la persona con suministro de vitamina A',['all'=>[['field'=>71,'op'=>'in','values'=>['1','16','17','18','20','21']],['any'=>[['age_months'=>true,'op'=>'lt','value'=>24],['age_months'=>true,'op'=>'gt','value'=>63]]]]],71),

            self::forbidden('Error069','Si registra tamizaje de cáncer de cuello uterino, la edad debe ser >=10 años y el sexo F',['all'=>[['field'=>86,'op'=>'neq','value'=>'0'],['any'=>[['field'=>10,'op'=>'neq','value'=>'F'],['age_months'=>true,'op'=>'lt','value'=>120]]]]],86),
            self::forbidden('Error070','Si registra fecha de tamizaje de cáncer de cuello uterino, la edad debe ser >=10 años',['all'=>[['field'=>87,'op'=>'neq','value'=>'1845-01-01'],['age_months'=>true,'op'=>'lt','value'=>120]]],87),
            self::forbidden('Error071','Si registra fecha de tamizaje de cáncer de cuello uterino, el sexo debe ser F',['all'=>[['field'=>87,'op'=>'neq','value'=>'1845-01-01'],['field'=>10,'op'=>'neq','value'=>'F']]],87),
            self::forbidden('Error072','Si registra resultado de tamizaje de cáncer de cuello uterino, la edad debe ser >=10 años',['all'=>[['field'=>88,'op'=>'neq','value'=>'0'],['age_months'=>true,'op'=>'lt','value'=>120]]],88),
            self::forbidden('Error073','Si registra resultado de tamizaje de cáncer de cuello uterino, el sexo debe ser F',['all'=>[['field'=>88,'op'=>'neq','value'=>'0'],['field'=>10,'op'=>'neq','value'=>'F']]],88),
            self::forbidden('Error074','Si registra calidad de muestra de citología, la edad debe ser >=10 años',['all'=>[['field'=>89,'op'=>'in','values'=>['1','2','3','4','999']],['age_months'=>true,'op'=>'lt','value'=>120]]],89),
            self::forbidden('Error075','Si registra calidad de muestra de citología, el sexo debe ser F',['all'=>[['field'=>89,'op'=>'neq','value'=>'0'],['field'=>10,'op'=>'neq','value'=>'F']]],89),
            self::forbidden('Error076','Si registra IPS de tamizaje de cáncer de cuello uterino, la edad debe ser mayor a 10 años',['all'=>[['field'=>90,'op'=>'neq','value'=>'0'],['age_months'=>true,'op'=>'lte','value'=>120]]],90),
            self::forbidden('Error077','Si registra IPS de tamizaje de cáncer de cuello uterino, el sexo debe ser F',['all'=>[['field'=>90,'op'=>'neq','value'=>'0'],['field'=>10,'op'=>'neq','value'=>'F']]],90),
            self::forbidden('Error078','Si registra fecha de colposcopia, la edad debe ser >=10 años',['all'=>[['field'=>91,'op'=>'neq','value'=>'1845-01-01'],['age_months'=>true,'op'=>'lt','value'=>120]]],91),

            self::forbidden('Error082','Si registra fecha de biopsia cervicouterina, la edad debe ser mayor a 10 años',['all'=>[['field'=>93,'op'=>'neq','value'=>'1845-01-01'],['age_months'=>true,'op'=>'lte','value'=>120]]],93),
            self::forbidden('Error083','Si registra fecha de biopsia cervicouterina, el sexo debe ser F',['all'=>[['field'=>93,'op'=>'neq','value'=>'1845-01-01'],['field'=>10,'op'=>'neq','value'=>'F']]],93),
            self::forbidden('Error084','Si registra resultado de biopsia cervicouterina, la edad debe ser mayor a 10 años',['all'=>[['field'=>94,'op'=>'in','values'=>['1','3','4','5','6','21']],['age_months'=>true,'op'=>'lte','value'=>120]]],94),
            self::forbidden('Error085','Si registra resultado de biopsia cervical, el sexo debe ser F',['all'=>[['field'=>94,'op'=>'neq','value'=>'0'],['field'=>10,'op'=>'neq','value'=>'F']]],94),

            self::forbidden('Error088','Registre no aplica en Fecha de mamografía si la edad es menor a 35 años',['all'=>[['field'=>96,'op'=>'neq','value'=>'1845-01-01'],['age_months'=>true,'op'=>'lt','value'=>420]]],96),
            self::forbidden('Error089','Si registra fecha de mamografía, el sexo debe ser F',['all'=>[['field'=>96,'op'=>'neq','value'=>'1845-01-01'],['field'=>10,'op'=>'neq','value'=>'F']]],96),
            self::forbidden('Error090','Si registra resultado de mamografía, la edad debe ser >=35 años',['all'=>[['field'=>97,'op'=>'neq','value'=>'0'],['age_months'=>true,'op'=>'lt','value'=>420]]],97),
            self::forbidden('Error091','Si registra resultado de mamografía, el sexo debe ser F',['all'=>[['field'=>97,'op'=>'neq','value'=>'0'],['field'=>10,'op'=>'neq','value'=>'F']]],97),
            self::forbidden('Error094','Si registra fecha de toma de biopsia de mama válida, el sexo debe ser F',['all'=>[['field'=>99,'op'=>'gt','value'=>'1900-01-01'],['field'=>10,'op'=>'neq','value'=>'F']]],99),
            self::forbidden('Error095','Si registra fecha de resultado de biopsia de mama válida, el sexo debe ser F',['all'=>[['field'=>100,'op'=>'gt','value'=>'1900-01-01'],['field'=>10,'op'=>'neq','value'=>'F']]],100),
            self::forbidden('Error096','Si registra resultado de biopsia de mama, el sexo debe ser F',['all'=>[['field'=>101,'op'=>'in','values'=>['1','2','3','4','5','21']],['field'=>10,'op'=>'neq','value'=>'F']]],101),

            ['code'=>'Error653','severity'=>'ERROR','operation'=>'in','variable'=>113,'values'=>['1','2','3','4','21'],'message'=>'Error en valores permitidos - Resultado de baciloscopia diagnóstico'],
            ['code'=>'Error655','severity'=>'ERROR','operation'=>'in','variable'=>114,'values'=>['0','4','5','6','21'],'message'=>'Error en valores permitidos - Clasificación del riesgo cardiovascular'],
            ['code'=>'Error656','severity'=>'ERROR','operation'=>'in','variable'=>115,'values'=>['0'],'message'=>'Error en valores permitidos - Tratamiento para sífilis gestacional'],
            ['code'=>'Error657','severity'=>'ERROR','operation'=>'in','variable'=>116,'values'=>['0'],'message'=>'Error en valores permitidos - Tratamiento para sífilis congénita'],
            ['code'=>'Error665','severity'=>'ERROR','operation'=>'in','variable'=>117,'values'=>['0','4','5','6','21'],'message'=>'Error en valores permitidos - Clasificación de riesgo metabólico'],
            ['code'=>'Error676','severity'=>'ERROR','operation'=>'length_by_value','variable'=>4,'selector_variable'=>3,'length_map'=>[
                'CC'=>[['max'=>10]],'TI'=>[['max'=>11]],'CE'=>[['min'=>3,'max'=>7]],'CD'=>[['max'=>11]],'PA'=>[['min'=>3,'max'=>16]],'SC'=>[['max'=>9]],'PE'=>[['min'=>3,'max'=>15]],
            ],'message'=>'La longitud del número de identificación no corresponde con el tipo de identificación'],
            ['code'=>'Error677','severity'=>'ERROR','operation'=>'date_before','variable'=>9,'date'=>'1900-01-01','message'=>'No se permite el registro de estos comodines en Fecha Nacimiento'],
            ['code'=>'Error678','severity'=>'ERROR','operation'=>'length_range_by_value','variable'=>102,'allowed_values'=>['0','21'],'allowed_lengths'=>[12],'allowed_pattern'=>'/^\\d{12}$/','message'=>'Solo se permite el registro de los valores 0, 21 o un valor de 12 dígitos de longitud'],
        ];
    }

    private static function forbidden(string $code, string $message, array $when, int $variable): array
    {
        return ['code'=>$code,'severity'=>'ERROR','operation'=>'forbidden_when','variable'=>$variable,'when'=>$when,'message'=>$message];
    }

    public static function pendingExternalCatalog(): array
    {
        return [
            ['code'=>'Error021','reason'=>'Requiere catálogo externo REPS del MSPS para validar existencia de la IPS primaria.'],
            ['code'=>'Error022','reason'=>'Requiere catálogo externo REPS del MSPS para validar IPS de tamizaje de cuello uterino.'],
        ];
    }

    public static function pendingSourceAmbiguities(): array
    {
        return [
            ['code'=>'Error079','reason'=>'La validación textual indica variable 90, mientras la descripción y variables relacionadas indican variable 91. Se mantiene pendiente hasta resolver la discrepancia de fuente.'],
        ];
    }
}
