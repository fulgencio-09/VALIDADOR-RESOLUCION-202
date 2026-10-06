<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedAdditionalRuleCatalog;
use App\Domain\Res202\Validation\RuleEngine;
use PHPUnit\Framework\TestCase;

final class RpedAdditionalRuleCatalogTest extends TestCase
{
    private RuleEngine $engine;

    protected function setUp(): void { $this->engine = new RuleEngine(); }

    public function test_rules_are_executable(): void
    {
        $codes=array_column(RpedAdditionalRuleCatalog::executable(),'code');
        self::assertSame(['Error220','Error222','Error223','Error227','Error232','Error237','Error242','Error243','Error244'],$codes);
    }

    public function test_error220_rejects_scientific_notation_and_accepts_identifier_characters(): void
    {
        $bad=$this->record([4=>'2.60874E+13']);
        $good=$this->record([4=>'1234567890']);
        self::assertContains('Error220',$this->codes($bad));
        self::assertNotContains('Error220',$this->codes($good));
    }

    public function test_error222_and_223_cover_gestation_age_boundaries(): void
    {
        self::assertContains('Error222',$this->codes($this->record([9=>'2018-01-01',14=>'2']), ['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error222',$this->codes($this->record([9=>'1960-01-01',14=>'2']), ['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error223',$this->codes($this->record([9=>'1980-01-01',10=>'F',14=>'0']), ['cutoff_date'=>'2026-09-30']));
    }

    public function test_error227_checks_mental_test_against_age(): void
    {
        self::assertContains('Error227',$this->codes($this->record([9=>'2010-01-01',16=>'5']), ['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error227',$this->codes($this->record([9=>'1950-01-01',16=>'0']), ['cutoff_date'=>'2026-09-30']));
    }

    public function test_error232_and_237_enforce_related_fields(): void
    {
        self::assertContains('Error232',$this->codes($this->record([18=>'1',113=>'4',112=>'2026-01-01'])));
        self::assertContains('Error237',$this->codes($this->record([9=>'1990-01-01',10=>'F',22=>'4',64=>'2026-01-01']), ['cutoff_date'=>'2026-09-30']));
    }

    public function test_error242_uses_cutoff_plus_280_days(): void
    {
        self::assertNotContains('Error242',$this->codes($this->record([33=>'2027-07-07']), ['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error242',$this->codes($this->record([33=>'2027-07-08']), ['cutoff_date'=>'2026-09-30']));
    }

    public function test_error243_compares_two_valid_dates_and_ignores_wildcards(): void
    {
        self::assertContains('Error243',$this->codes($this->record([33=>'2026-09-10',56=>'2026-09-10'])));
        self::assertNotContains('Error243',$this->codes($this->record([33=>'1845-01-01',56=>'1845-01-01'])));
    }

    public function test_error244_requires_no_aplica_when_not_pregnant(): void
    {
        self::assertContains('Error244',$this->codes($this->record([14=>'2',23=>'1'])));
        self::assertNotContains('Error244',$this->codes($this->record([14=>'2',23=>'0',35=>'0',59=>'0',60=>'0',61=>'0',33=>'1845-01-01',56=>'1845-01-01',58=>'1845-01-01'])));
    }

    private function codes(array $record, array $context=[]): array { return array_column($this->engine->validate($record,RpedAdditionalRuleCatalog::executable(),$context),'code'); }

    private function record(array $overrides=[]): array
    {
        $r=array_fill(0,119,'');
        foreach([3=>'CC',4=>'1234567890',9=>'1990-01-01',10=>'F',14=>'0',16=>'0',18=>'2',22=>'0',23=>'0',33=>'1845-01-01',35=>'0',56=>'1845-01-01',58=>'1845-01-01',59=>'0',60=>'0',61=>'0',64=>'1845-01-01',112=>'1845-01-01',113=>'4'] as $k=>$v)$r[$k]=$v;
        foreach($overrides as $k=>$v)$r[$k]=$v;
        return $r;
    }
}
