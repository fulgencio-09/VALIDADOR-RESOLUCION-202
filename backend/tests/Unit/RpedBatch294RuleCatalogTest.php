<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedBatch294RuleCatalog;
use App\Domain\Res202\Validation\RuleEngine;
use PHPUnit\Framework\TestCase;

final class RpedBatch294RuleCatalogTest extends TestCase
{
    private RuleEngine $engine;

    protected function setUp(): void { $this->engine = new RuleEngine(); }

    public function test_batch_contains_all_implemented_codes(): void
    {
        $codes=array_column(RpedBatch294RuleCatalog::executable(),'code');
        self::assertCount(48,$codes);
        foreach(['Error294','Error296','Error299','Error300','Error301','Error304','Error305','Error306','Warning307','Error308','Error309','Error318','Error328','Error329','Error341','Error344','Error346','Error350','Error352','Error354','Error355','Error359','Error361','Error362','Error364','Error367','Error368','Error369','Error371','Error375','Error379','Error380','Error381','Error382','Error383','Error384','Error385','Error386','Error387','Error388','Error389','Error390','Error391','Error392','Error393','Error394','Error395','Error396','Error398','Error399'] as $code){self::assertContains($code,$codes);}
    }

    public function test_age_and_cross_field_rules(): void
    {
        self::assertContains('Error294',$this->codes($this->record([9=>'2018-01-01',49=>'2026-01-01']),['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error296',$this->codes($this->record([49=>'2026-01-01',50=>'1845-01-01'])));
        self::assertContains('Error299',$this->codes($this->record([9=>'1960-01-01',50=>'2026-01-01']),['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error300',$this->codes($this->record([49=>'2026-09-02',50=>'2026-09-01'])));
        self::assertContains('Error301',$this->codes($this->record([49=>'1845-01-01',50=>'2026-09-01'])));
        self::assertContains('Error318',$this->codes($this->record([56=>'2026-09-02',58=>'2026-09-01'])));
    }

    public function test_method_and_screening_rules(): void
    {
        self::assertContains('Error306',$this->codes($this->record([54=>'1',55=>'1845-01-01'])));
        self::assertContains('Error308',$this->codes($this->record([9=>'2000-01-01',54=>'0']),['cutoff_date'=>'2026-09-30']));
        self::assertContains('Error309',$this->codes($this->record([54=>'0',55=>'2026-01-01'])));
        self::assertContains('Error350',$this->codes($this->record([84=>'2026-01-01',85=>'0'])));
        self::assertContains('Error361',$this->codes($this->record([96=>'2026-01-01',97=>'0'])));
        self::assertContains('Error364',$this->codes($this->record([9=>'2020-01-01',97=>'1']),['cutoff_date'=>'2026-09-30']));
    }

    public function test_biopsy_creatinine_and_bacilloscopy_rules(): void
    {
        self::assertContains('Error368',$this->codes($this->record([99=>'2026-09-02',100=>'2026-09-01'])));
        self::assertContains('Error369',$this->codes($this->record([100=>'1845-01-01',101=>'1'])));
        self::assertContains('Error371',$this->codes($this->record([106=>'2026-01-01',107=>'998'])));
        self::assertContains('Error375',$this->codes($this->record([112=>'2026-01-01',113=>'21'])));
    }

    public function test_wildcard_rules_accept_allowed_and_reject_disallowed(): void
    {
        self::assertNotContains('Error381',$this->codes($this->record([29=>'1800-01-01'])));
        self::assertContains('Error381',$this->codes($this->record([29=>'1805-01-01'])));
        self::assertNotContains('Error380',$this->codes($this->record([33=>'1845-01-01'])));
        self::assertContains('Error380',$this->codes($this->record([33=>'1805-01-01'])));
        self::assertNotContains('Error390',$this->codes($this->record([58=>'1845-01-01'])));
        self::assertContains('Error390',$this->codes($this->record([58=>'1835-01-01'])));
    }

    public function test_warning307_severity_is_warning(): void
    {
        $results=$this->engine->validate($this->record([9=>'2010-01-01',54=>'1',55=>'2026-01-01']),RpedBatch294RuleCatalog::executable(),['cutoff_date'=>'2026-09-30']);
        $warning=array_values(array_filter($results,static fn(array$r):bool=>$r['code']==='Warning307'));
        self::assertCount(1,$warning);
        self::assertSame('WARNING',$warning[0]['severity']);
    }

    private function codes(array $record,array $context=[]):array{return array_column($this->engine->validate($record,RpedBatch294RuleCatalog::executable(),$context),'code');}

    private function record(array $overrides=[]):array
    {
        $r=array_fill(0,119,'');
        foreach([3=>'CC',4=>'1234567890',9=>'1990-01-01',10=>'F',14=>'0',16=>'0',18=>'2',22=>'0',23=>'1',29=>'2026-01-01',31=>'2026-01-01',33=>'1845-01-01',49=>'1845-01-01',50=>'1845-01-01',53=>'1845-01-01',54=>'0',55=>'1845-01-01',56=>'1845-01-01',58=>'1845-01-01',59=>'1',60=>'1',61=>'1',64=>'1845-01-01',70=>'0',71=>'0',78=>'1845-01-01',79=>'0',80=>'1845-01-01',81=>'0',82=>'1845-01-01',83=>'0',84=>'1845-01-01',85=>'0',87=>'1845-01-01',88=>'0',89=>'0',90=>'0',93=>'1845-01-01',94=>'0',96=>'1845-01-01',97=>'0',99=>'1845-01-01',100=>'1845-01-01',101=>'0',106=>'1845-01-01',107=>'0',112=>'1845-01-01',113=>'1'] as $k=>$v)$r[$k]=$v;
        foreach($overrides as $k=>$v)$r[$k]=$v;
        return $r;
    }
}
