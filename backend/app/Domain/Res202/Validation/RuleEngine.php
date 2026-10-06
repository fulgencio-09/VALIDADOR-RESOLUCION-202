<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

use DateTimeImmutable;

final class RuleEngine
{
    public function validate(array $record, array $rules, array $context = []): array
    {
        $results = [];
        $context = $this->enrichContext($record, $context);
        foreach ($rules as $rule) {
            if (($rule['active'] ?? true) !== true) continue;
            $operation=(string)($rule['operation']??'');$variable=(int)($rule['variable']??-1);$value=$record[$variable]??null;
            $failed=match($operation){
                'required'=>$value===null||$value==='', 'in'=>!$this->inAllowedValues($value,$rule['values']??[]),
                'equals_when'=>$this->equalsWhen($record,$rule),'equals_when_any'=>$this->equalsWhenAny($record,$rule),'not_equals_when'=>$this->notEqualsWhen($record,$rule),
                'date_not_before'=>$this->dateNotBefore($value,$rule['date']??null),'date_not_after'=>$this->dateNotAfter($value,$rule['date']??null),'date_before'=>$this->dateBefore($value,$rule['date']??null),
                'date_before_birth'=>$this->dateBeforeBirth($record,$value,$rule),'date_after_cutoff'=>$this->dateAfterCutoff($value,$context['cutoff_date']??null),
                'date_after_cutoff_plus_days'=>$this->dateAfterCutoffPlusDays($value,$context['cutoff_date']??null,(int)($rule['days']??0)),'date_relation'=>$this->dateRelation($record,$rule),
                'date_value_valid'=>$this->dateValueInvalid($value,$rule['allowed_wildcards']??[]),'wildcard_allowed'=>$this->wildcardInvalid($value,$rule['allowed_wildcards']??[]),
                'value_equals'=>(string)$value===(string)($rule['expected']??''),'catalog_exists'=>$this->catalogMissing($value,(string)($rule['catalog']??''),$context),
                'components_range'=>$this->componentsRangeInvalid($record,$rule,$context),'length_by_value'=>$this->lengthByValue($record,$rule),'length_range_by_value'=>$this->lengthRangeByValue($record,$rule),
                'length_exact'=>$this->lengthExact($value,(int)($rule['length']??0)),'regex'=>$this->regexFails($value,(string)($rule['pattern']??'')),
                'forbidden_when'=>$this->matchesCondition($record,$rule['when']??[],$context),'conditional'=>$this->conditionalFails($record,$rule,$context),default=>false,
            };
            if($failed)$results[]=['code'=>(string)$rule['code'],'severity'=>strtoupper((string)($rule['severity']??'ERROR')),'variable'=>$variable>=0?$variable:null,'message'=>(string)($rule['message']??'Regla no cumplida.'),'value'=>$value];
        }
        return $results;
    }

    private function enrichContext(array $record,array $context):array{if(!isset($context['cutoff_date'])||!is_string($context['cutoff_date']))return$context;if(isset($context['age_months'],$context['age_years'],$context['age_days']))return$context;$birth=$this->parseDate($record[9]??null);$cutoff=$this->parseDate($context['cutoff_date']);if($birth===null||$cutoff===null||$birth>$cutoff||$birth<new DateTimeImmutable('1900-01-01'))return$context;$diff=$birth->diff($cutoff);$context['age_years']=$diff->y;$context['age_months']=($diff->y*12)+$diff->m;$context['age_days']=$diff->days??0;return$context;}
    private function parseDate(mixed $value):?DateTimeImmutable{if($value===null||$value==='')return null;$date=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$value);return$date===false?null:$date;}
    private function dateAfterCutoff(mixed $value,mixed $cutoff):bool{$a=$this->parseDate($value);$b=$this->parseDate($cutoff);return$a!==null&&$b!==null&&$a>$b;}
    private function dateAfterCutoffPlusDays(mixed $value,mixed $cutoff,int $days):bool{$a=$this->parseDate($value);$b=$this->parseDate($cutoff);return$a!==null&&$b!==null&&$a>$b->modify(sprintf('+%d days',$days));}
    private function dateRelation(array $record,array $rule):bool{$a=$this->parseDate($record[(int)($rule['variable']??-1)]??null);$b=$this->parseDate($record[(int)($rule['other_variable']??-1)]??null);if($a===null||$b===null)return false;if(isset($rule['min_valid_date'])&&$rule['min_valid_date']!==null){$min=$this->parseDate($rule['min_valid_date']);if($min!==null&&($a<=$min||$b<=$min))return false;}return match((string)($rule['relation']??'lt')){'lt'=>$a<$b,'lte'=>$a<=$b,'gt'=>$a>$b,'gte'=>$a>=$b,'eq'=>$a==$b,'neq'=>$a!=$b,default=>false};}
    private function dateValueInvalid(mixed $value,array $allowedWildcards):bool{if($value===null||$value==='')return false;$v=(string)$value;if(in_array($v,array_map('strval',$allowedWildcards),true))return false;$d=DateTimeImmutable::createFromFormat('!Y-m-d',$v);$e=DateTimeImmutable::getLastErrors();if($d===false)return true;if(is_array($e)&&(($e['warning_count']??0)>0||($e['error_count']??0)>0))return true;return$d->format('Y-m-d')!==$v;}
    private function wildcardInvalid(mixed $value,array $allowedWildcards):bool{if($value===null||$value==='')return false;$v=(string)$value;$known=['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01'];return in_array($v,$known,true)&&!in_array($v,array_map('strval',$allowedWildcards),true);}
    private function catalogMissing(mixed $value,string $catalog,array $context):bool{if($value===null||$value===''||$catalog==='')return false;$catalogs=$context['catalogs']??null;if(!is_array($catalogs)||!array_key_exists($catalog,$catalogs))return false;$allowed=$catalogs[$catalog];if(!is_array($allowed))return false;return!in_array((string)$value,array_map('strval',$allowed),true);}
    private function componentsRangeInvalid(array $record,array $rule,array $context):bool{if(!$this->matchesCondition($record,['all'=>$rule['when']??[]],$context))return false;$v=(string)($record[(int)($rule['variable']??-1)]??'');if(strlen($v)!==12||!ctype_digit($v))return true;$min=(int)($rule['min']??0);$max=(int)($rule['max']??99);for($i=0;$i<12;$i+=2){$c=(int)substr($v,$i,2);if($c<$min||$c>$max)return true;}return false;}
    private function dateBeforeBirth(array $record,mixed $value,array $rule):bool{if($value===null||$value==='')return false;$w=array_map('strval',$rule['ignore_values']??['1800-01-01','1805-01-01','1810-01-01','1825-01-01','1830-01-01','1835-01-01','1845-01-01']);if(in_array((string)$value,$w,true))return false;$b=$record[(int)($rule['birth_variable']??9)]??null;if($b===null||$b===''||in_array((string)$b,$w,true))return false;if(isset($rule['min_valid_date'])&&$rule['min_valid_date']!==null&&(string)$value<=(string)$rule['min_valid_date'])return false;$a=$this->parseDate($value);$bd=$this->parseDate($b);if($a===null||$bd===null)return false;return(bool)($rule['inclusive']??true)?$a<=$bd:$a<$bd;}
    private function matchesCondition(array $record,array $condition,array $context):bool{if(isset($condition['all'])){foreach($condition['all'] as $c)if(!$this->matchesCondition($record,$c,$context))return false;return true;}if(isset($condition['any'])){foreach($condition['any'] as $c)if($this->matchesCondition($record,$c,$context))return true;return false;}if(($condition['always']??true)===false)return false;$a=$this->conditionValue($record,$condition,$context);$op=(string)($condition['op']??'eq');$e=$condition['value']??null;return match($op){'eq'=>(string)$a===(string)$e,'neq'=>(string)$a!==(string)$e,'in'=>in_array((string)$a,array_map('strval',$condition['values']??[]),true),'not_in'=>!in_array((string)$a,array_map('strval',$condition['values']??[]),true),'lt'=>$this->compare($a,$e)<0,'lte'=>$this->compare($a,$e)<=0,'gt'=>$this->compare($a,$e)>0,'gte'=>$this->compare($a,$e)>=0,default=>false};}
    private function conditionValue(array $record,array $condition,array $context):mixed{if(array_key_exists('field',$condition))return$record[(int)$condition['field']]??null;if(array_key_exists('length_of',$condition))return strlen((string)($record[(int)$condition['length_of']]??''));if(array_key_exists('age_years',$condition))return$context['age_years']??null;if(array_key_exists('age_months',$condition))return$context['age_months']??null;if(array_key_exists('age_days',$condition))return$context['age_days']??null;return null;}
    private function compare(mixed $a,mixed $e):int{if($a===null||$e===null||$a==='')return 0;if(is_numeric($a)&&is_numeric($e))return(float)$a<=>(float)$e;return(string)$a<=>(string)$e;}
    private function conditionalFails(array $record,array $rule,array $context):bool{return$this->matchesCondition($record,$rule['when']??[],$context)&&!$this->matchesCondition($record,$rule['require']??[],$context);}
    private function inAllowedValues(mixed $value,array $allowed):bool{if($value===null||$value==='')return true;return in_array((string)$value,array_map('strval',$allowed),true);}
    private function equalsWhen(array $record,array $rule):bool{if((string)($record[(int)($rule['when_variable']??-1)]??'')!==(string)($rule['when_value']??''))return false;return(string)($record[(int)$rule['variable']]??'')!==(string)($rule['expected']??'');}
    private function equalsWhenAny(array $record,array $rule):bool{$w=array_filter(explode('|',(string)($rule['when_value']??'')),static fn(string$v):bool=>$v!=='');if(!in_array((string)($record[(int)($rule['when_variable']??-1)]??''),$w,true))return false;return(string)($record[(int)$rule['variable']]??'')!==(string)($rule['expected']??'');}
    private function notEqualsWhen(array $record,array $rule):bool{if((string)($record[(int)($rule['when_variable']??-1)]??'')===(string)($rule['when_value']??''))return false;return(string)($record[(int)$rule['variable']]??'')===(string)($rule['expected']??'');}
    private function dateNotBefore(mixed $value,mixed $date):bool{return$value!==null&&$value!==''&&$date!==null&&$date!==''&&(string)$value<(string)$date;}
    private function dateNotAfter(mixed $value,mixed $date):bool{return$value!==null&&$value!==''&&$date!==null&&$date!==''&&(string)$value>(string)$date;}
    private function dateBefore(mixed $value,mixed $date):bool{$a=$this->parseDate($value);$b=$this->parseDate($date);return$a!==null&&$b!==null&&$a<$b;}
    private function lengthByValue(array $record,array $rule):bool{$s=(string)($record[(int)($rule['selector_variable']??-1)]??'');$v=(string)($record[(int)($rule['variable']??-1)]??'');if(!array_key_exists($s,$rule['length_map']??[]))return false;$l=strlen($v);foreach($rule['length_map'][$s] as $c){$min=$c['min']??null;$max=$c['max']??null;if(($min===null||$l>=(int)$min)&&($max===null||$l<=(int)$max))return false;}return true;}
    private function lengthRangeByValue(array $record,array $rule):bool{$v=(string)($record[(int)($rule['variable']??-1)]??'');if(in_array($v,array_map('strval',$rule['allowed_values']??[]),true))return false;if(!in_array(strlen($v),array_map('intval',$rule['allowed_lengths']??[]),true))return true;$p=$rule['allowed_pattern']??null;return$p!==null&&preg_match((string)$p,$v)!==1;}
    private function lengthExact(mixed $value,int $length):bool{return$value!==null&&$value!==''&&strlen((string)$value)!==$length;}
    private function regexFails(mixed $value,string $pattern):bool{return$value!==null&&$value!==''&&$pattern!==''&&preg_match($pattern,(string)$value)!==1;}
}
