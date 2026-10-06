<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedBatch638RuleCatalog;
use App\Domain\Res202\Validation\RuleEngine;
use PHPUnit\Framework\TestCase;

final class RpedBatch638RuleCatalogTest extends TestCase
{
    private RuleEngine $engine;
    protected function setUp(): void { $this->engine = new RuleEngine(); }

    public function test_batch_contains_13_active_rules(): void
    {
        $codes=array_column(RpedBatch638RuleCatalog::executable(),'code');
        self::assertCount(13,$codes);
        foreach(['Error638','Error639','Error640','Error641','Error642','Error643','Error645','Error646','Error647','Error649','Error650','Error651','Error652'] as $code){self::assertContains($code,$codes);}
    }

    public function test_hemoglobin_rules(): void
    {
        self::assertContains('Error638',$this->codes($this->record([9=>'2010-01-01',10=>'F',103=>'1845-01-01']),['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error639',$this->codes($this->record([103=>'2026-01-01',104=>'0'])));
        self::assertContains('Error640',$this->codes($this->record([9=>'2010-01-01',10=>'F',103=>'1800-01-01',104=>'0']),['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error641',$this->codes($this->record([103=>'1845-01-01',104=>'998'])));
    }

    public function test_glycemia_and_creatinine_dates(): void
    {
        self::assertContains('Error642',$this->codes($this->record([105=>'2026-10-01']),['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error643',$this->codes($this->record([9=>'2020-01-01',105=>'2020-01-01'])));
        self::assertContains('Error645',$this->codes($this->record([106=>'1800-01-01',107=>'0'])));
        self::assertContains('Error646',$this->codes($this->record([9=>'1990-01-01',106=>'1845-01-01',107=>'0']),['cutoff_date'=>'2026-09-30']));
    }

    public function test_psa_and_hba1c_rules(): void
    {
        self::assertContains('Error647',$this->codes($this->record([108=>'2026-01-01'])));
        self::assertContains('Error649',$this->codes($this->record([9=>'1990-01-01',10=>'F',73=>'2026-01-01',109=>'0']),['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error650',$this->codes($this->record([73=>'2026-01-01',109=>'998'])));
        self::assertContains('Error651',$this->codes($this->record([10=>'F',73=>'2026-01-01',109=>'0'])));
        self::assertContains('Error652',$this->codes($this->record([9=>'2000-01-01',10=>'M',73=>'2026-01-01',109=>'0']),['cutoff_date'=>'2026-09-30']));
    }

    private function codes(array $record,array $context=[]):array{return array_column($this->engine->validate($record,RpedBatch638RuleCatalog::executable(),$context),'code');}
    private function record(array $overrides=[]):array
    {
        $r=array_fill(0,119,'');
        foreach([3=>'CC',4=>'1234567890',9=>'1990-01-01',10=>'M',73=>'1845-01-01',103=>'1845-01-01',104=>'0',105=>'1845-01-01',106=>'1845-01-01',107=>'0',108=>'1845-01-01',109=>'0'] as $k=>$v)$r[$k]=$v;
        foreach($overrides as $k=>$v)$r[$k]=$v;
        return $r;
    }
}
