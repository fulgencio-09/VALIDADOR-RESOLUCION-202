<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedBatch600RuleCatalog;
use App\Domain\Res202\Validation\RuleEngine;
use PHPUnit\Framework\TestCase;

final class RpedBatch600RuleCatalogTest extends TestCase
{
    private RuleEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new RuleEngine();
    }

    public function test_batch_contains_32_rules(): void
    {
        $codes = array_column(RpedBatch600RuleCatalog::executable(), 'code');
        self::assertCount(32, $codes);
        foreach (range(600, 631) as $number) {
            self::assertContains('Error' . $number, $codes);
        }
    }

    public function test_hepatitis_syphilis_and_hiv_rules(): void
    {
        self::assertContains('Error600', $this->codes($this->record([78=>'2026-01-01',79=>'0'])));
        self::assertContains('Error601', $this->codes($this->record([79=>'9'])));
        self::assertContains('Error602', $this->codes($this->record([80=>'1800-01-01',81=>'0'])));
        self::assertContains('Error603', $this->codes($this->record([80=>'2026-01-01',81=>'0'])));
        self::assertContains('Error604', $this->codes($this->record([81=>'9'])));
        self::assertContains('Error605', $this->codes($this->record([82=>'1800-01-01',83=>'0'])));
        self::assertContains('Error606', $this->codes($this->record([82=>'1845-01-01',83=>'4'])));
        self::assertContains('Error607', $this->codes($this->record([83=>'9'])));
    }

    public function test_cervical_screening_rules(): void
    {
        self::assertContains('Error608', $this->codes($this->record([85=>'9'])));
        self::assertContains('Error609', $this->codes($this->record([84=>'2026-01-01',85=>'21'])));
        self::assertContains('Error610', $this->codes($this->record([86=>'1',87=>'1845-01-01'])));
        self::assertContains('Error611', $this->codes($this->record([86=>'1',88=>'19'])));
        self::assertContains('Error612', $this->codes($this->record([86=>'2',88=>'1'])));
        self::assertContains('Error613', $this->codes($this->record([86=>'0',87=>'2026-01-01',88=>'0'])));
        self::assertContains('Error614', $this->codes($this->record([86=>'21',87=>'1845-01-01',88=>'21'])));
        self::assertContains('Error615', $this->codes($this->record([89=>'4',88=>'17'])));
        self::assertContains('Error616', $this->codes($this->record([88=>'22'])));
    }

    public function test_ldl_biopsy_hdl_mammography_and_triglyceride_rules(): void
    {
        self::assertContains('Error617', $this->codes($this->record([72=>'2026-01-01',92=>'0'])));
        self::assertContains('Error618', $this->codes($this->record([72=>'1800-01-01',92=>'0'])));
        self::assertContains('Error619', $this->codes($this->record([9=>'1990-01-01',72=>'1845-01-01',92=>'0'])));
        self::assertContains('Error620', $this->codes($this->record([94=>'9'])));
        self::assertContains('Error621', $this->codes($this->record([93=>'2026-01-01',94=>'0'])));
        self::assertContains('Error622', $this->codes($this->record([93=>'1845-01-01',94=>'21'])));
        self::assertContains('Error623', $this->codes($this->record([111=>'2026-01-01',95=>'0'])));
        self::assertContains('Error624', $this->codes($this->record([111=>'1800-01-01',95=>'0'])));
        self::assertContains('Error625', $this->codes($this->record([9=>'1990-01-01',111=>'1845-01-01',95=>'0'])));
        self::assertContains('Error626', $this->codes($this->record([9=>'1970-01-01',10=>'F',96=>'1800-01-01',97=>'0'])));
        self::assertContains('Error627', $this->codes($this->record([96=>'1845-01-01',97=>'1'])));
        self::assertContains('Error628', $this->codes($this->record([97=>'9'])));
        self::assertContains('Error629', $this->codes($this->record([118=>'2026-01-01',98=>'0'])));
        self::assertContains('Error630', $this->codes($this->record([118=>'1800-01-01',98=>'0'])));
        self::assertContains('Error631', $this->codes($this->record([9=>'1990-01-01',118=>'1845-01-01',98=>'0'])));
    }

    /** @return array<int,string> */
    private function codes(array $record): array
    {
        return array_column($this->engine->validate($record, RpedBatch600RuleCatalog::executable(), []), 'code');
    }

    private function record(array $overrides = []): array
    {
        $record = array_fill(0, 119, '');
        foreach ([3=>'CC',4=>'1234567890',9=>'1990-01-01',10=>'M',72=>'1845-01-01',73=>'1845-01-01',78=>'1845-01-01',79=>'0',80=>'1845-01-01',81=>'0',82=>'1845-01-01',83=>'0',84=>'1845-01-01',85=>'0',86=>'0',87=>'1845-01-01',88=>'0',89=>'0',90=>'999',92=>'0',93=>'1845-01-01',94=>'0',95=>'0',96=>'1845-01-01',97=>'0',98=>'0',111=>'1845-01-01',118=>'1845-01-01'] as $key=>$value) {
            $record[$key] = $value;
        }
        foreach ($overrides as $key=>$value) {
            $record[$key] = $value;
        }
        return $record;
    }
}
