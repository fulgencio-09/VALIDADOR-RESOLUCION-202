<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedBatch535RuleCatalog;
use App\Domain\Res202\Validation\RuleEngine;
use PHPUnit\Framework\TestCase;

final class RpedBatch535RuleCatalogTest extends TestCase
{
    private RuleEngine $engine;
    protected function setUp(): void { $this->engine = new RuleEngine(); }

    public function test_batch_contains_six_rules(): void
    {
        $codes=array_column(RpedBatch535RuleCatalog::executable(),'code');
        self::assertSame(['Error535','Error536','Error537','Error538','Error539','Error540'],$codes);
    }

    public function test_colonoscopy_rules(): void
    {
        self::assertContains('Error535',$this->codes($this->record([66=>'2026-01-01',36=>'0'])));
        self::assertContains('Error536',$this->codes($this->record([9=>'1980-01-01',66=>'1800-01-01',36=>'0']),['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error537',$this->codes($this->record([9=>'1980-01-01',66=>'1845-01-01',36=>'2']),['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error538',$this->codes($this->record([36=>'1'])));
    }

    public function test_auditory_screening_rules(): void
    {
        self::assertContains('Error539',$this->codes($this->record([37=>'4',69=>'1845-01-01'])));
        self::assertContains('Error540',$this->codes($this->record([37=>'1'])));
    }

    private function codes(array $record,array $context=[]):array{return array_column($this->engine->validate($record,RpedBatch535RuleCatalog::executable(),$context),'code');}
    private function record(array $overrides=[]):array
    {
        $r=array_fill(0,119,'');
        foreach([3=>'CC',4=>'1234567890',9=>'1990-01-01',10=>'F',36=>'0',37=>'0',66=>'1845-01-01',69=>'1845-01-01'] as $k=>$v)$r[$k]=$v;
        foreach($overrides as $k=>$v)$r[$k]=$v;
        return $r;
    }
}
