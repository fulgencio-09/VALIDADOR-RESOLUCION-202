<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedClarifiedRuleCatalog;
use App\Domain\Res202\Validation\RpedCopConsistencyValidator;
use App\Domain\Res202\Validation\RuleEngine;
use PHPUnit\Framework\TestCase;

final class RpedClarifiedRuleCatalogTest extends TestCase
{
    private RuleEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new RuleEngine();
    }

    public function test_clarified_catalog_contains_all_requested_codes(): void
    {
        $codes = array_column(RpedClarifiedRuleCatalog::executable(), 'code');
        foreach ([572,632,633,634,635,636,637,666,667,669,670,673] as $code) {
            self::assertContains('Error'.$code, $codes);
        }
    }

    public function test_error572_depends_on_age(): void
    {
        self::assertContains('Error572', $this->codes($this->record([9=>'2018-01-01',46=>'0']), '2026-09-30'));
        self::assertContains('Error572', $this->codes($this->record([9=>'2010-01-01',46=>'3']), '2026-09-30'));
        self::assertNotContains('Error572', $this->codes($this->record([9=>'2020-01-01',46=>'3']), '2026-09-30'));
        self::assertNotContains('Error572', $this->codes($this->record([9=>'2010-01-01',46=>'0']), '2026-09-30'));
    }

    public function test_error632_and_633(): void
    {
        self::assertContains('Error632', $this->codes($this->record([100=>'2026-09-01',101=>'21']), '2026-09-30'));
        self::assertNotContains('Error632', $this->codes($this->record([100=>'1800-01-01',101=>'21']), '2026-09-30'));
        self::assertContains('Error633', $this->codes($this->record([101=>'9']), '2026-09-30'));
    }

    public function test_error634_and_635(): void
    {
        self::assertContains('Error634', $this->codes($this->record([9=>'2018-01-01',76=>'1800-01-01',102=>'0']), '2026-09-30'));
        self::assertNotContains('Error634', $this->codes($this->record([9=>'2018-01-01',76=>'1800-01-01',102=>'21']), '2026-09-30'));
        self::assertContains('Error635', $this->codes($this->record([76=>'2026-08-01',102=>'123']), '2026-09-30'));
    }

    public function test_cop_examples_and_consistency(): void
    {
        $validator = new RpedCopConsistencyValidator();
        foreach (['150400010020','200000000020'] as $cop) {
            $result = $validator->validate($this->record([9=>'2022-01-01',76=>'2026-08-01',102=>$cop]), 2, '2026-09-30');
            self::assertSame([], $result);
        }
        $adult = $validator->validate($this->record([9=>'1990-01-01',76=>'2026-08-01',102=>'320000000032']), 2, '2026-09-30');
        self::assertSame([], $adult);
        $invalid = $validator->validate($this->record([9=>'2022-01-01',76=>'2026-08-01',102=>'150400000020']), 2, '2026-09-30');
        self::assertSame('Error636', $invalid[0]['code']);
    }

    public function test_error666_667_669_670_673(): void
    {
        self::assertContains('Error666', $this->codes($this->record([118=>'2026-10-01']), '2026-09-30'));
        self::assertContains('Error667', $this->codes($this->record([9=>'1990-01-01',118=>'1989-12-31']), '2026-09-30'));
        self::assertContains('Error669', $this->codes($this->record([9=>'2020-01-01',77=>'1']), '2026-09-30'));
        self::assertContains('Error670', $this->codes($this->record([9=>'2022-01-01',77=>'0']), '2026-09-30'));
        self::assertContains('Error673', $this->codes($this->record([86=>'3',88=>'19',47=>'6']), '2026-09-30'));
    }

    private function codes(array $record, string $cutoff): array
    {
        return array_column($this->engine->validate($record, RpedClarifiedRuleCatalog::executable(), ['cutoff_date'=>$cutoff]), 'code');
    }

    private function record(array $overrides = []): array
    {
        $record = array_fill(0, 119, '');
        foreach ([3=>'CC',4=>'1234567890',9=>'1990-01-01',10=>'M',46=>'0',47=>'0',76=>'1845-01-01',77=>'0',86=>'0',88=>'0',100=>'1800-01-01',101=>'0',102=>'0',118=>'1845-01-01'] as $key=>$value) {
            $record[$key] = $value;
        }
        foreach ($overrides as $key=>$value) $record[$key] = $value;
        return $record;
    }
}
