<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

final class RpedValidator
{
    public function __construct(
        private readonly StructuralValidator $structuralValidator,
        private readonly RuleEngine $ruleEngine,
    ) {}

    /** @param array<int,array{name:string,length:int,type:string}> $variables
     *  @param array<int,array<string,mixed>> $rules */
    public function validate(string $content, array $variables, array $rules): array
    {
        $structural = $this->structuralValidator->validate($content, $variables);
        $lines = preg_split('/\r\n|\n|\r/', $content) ?: [];
        $lines = array_values(array_filter($lines, static fn(string $line): bool => $line !== ''));
        $business=[]; $records=0;
        $control=isset($lines[0])?explode('|',$lines[0]):[];
        $cutoffDate=(($control[0]??'')==='1')?($control[3]??null):null;
        $rules=array_merge($rules,RpedAdditionalRuleCatalog::executable());
        foreach($lines as $lineNumber=>$line){
            $fields=explode('|',$line);
            if(($fields[0]??'')!=='2'||count($fields)!==count($variables))continue;
            $record=[];foreach($fields as $index=>$value)$record[$index]=$value;
            $records++;$context=$cutoffDate!==null?['cutoff_date'=>$cutoffDate]:[];
            foreach($this->ruleEngine->validate($record,$rules,$context) as $result){$result['line']=$lineNumber+1;$business[]=$result;}
        }
        $results=array_merge($structural,$business);
        return ['valid'=>$results===[],'records'=>$records,'errors'=>count(array_filter($results,static fn(array$r):bool=>($r['severity']??'ERROR')!=='WARNING'),'warnings'=>count(array_filter($results,static fn(array$r):bool=>($r['severity']??'')==='WARNING'),'results'=>$results];
    }
}