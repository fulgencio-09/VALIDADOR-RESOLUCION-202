<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedExternalConsistencyRuleCatalog;
use App\Domain\Res202\Validation\RuleEngine;
use PHPUnit\Framework\TestCase;

final class RpedExternalConsistencyRuleCatalogTest extends TestCase
{
    private RuleEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new RuleEngine();
    }

    public function test_variable_89_zero_does_not_require_variable_90_to_match_variable_2(): void
    {
        $record = $this->record([2 => '123456789012', 89 => '0', 90 => '999999999999']);

        self::assertNotContains('Error021', $this->codes($record));
    }

    public function test_variable_89_999_does_not_require_variable_90_to_match_variable_2(): void
    {
        $record = $this->record([2 => '123456789012', 89 => '999', 90 => '888888888888']);

        self::assertNotContains('Error021', $this->codes($record));
    }

    public function test_variable_89_other_than_zero_or_999_requires_variable_90_equal_variable_2(): void
    {
        $record = $this->record([2 => '987654321000', 89 => '123', 90 => '987654321000']);

        self::assertNotContains('Error021', $this->codes($record));
    }

    public function test_error021_is_generated_when_variable_90_differs_from_variable_2(): void
    {
        $record = $this->record([2 => '987654321000', 89 => '123', 90 => '111111111111']);

        self::assertContains('Error021', $this->codes($record));
    }

    public function test_variable_2_is_not_restricted_to_a_fixed_code(): void
    {
        $record = $this->record([2 => '440900022701', 89 => '456', 90 => '440900022701']);
        $otherRecord = $this->record([2 => '110010001234', 89 => '456', 90 => '110010001234']);

        self::assertNotContains('Error021', $this->codes($record));
        self::assertNotContains('Error021', $this->codes($otherRecord));
    }

    public function test_external_catalog_contains_only_the_defined_internal_rule(): void
    {
        self::assertSame(['Error021'], array_column(RpedExternalConsistencyRuleCatalog::executable(), 'code'));
    }

    /** @return list<string> */
    private function codes(array $record): array
    {
        return array_column(
            $this->engine->validate(
                $record,
                RpedExternalConsistencyRuleCatalog::executable(),
                ['cutoff_date' => '2026-09-30', 'age_months' => 432, 'age_years' => 36, 'age_days' => 12960],
            ),
            'code',
        );
    }

    private function record(array $overrides = []): array
    {
        $record = array_fill(0, 119, '');
        $record[2] = '123456789012';
        $record[89] = '0';
        $record[90] = '0';

        foreach ($overrides as $variable => $value) {
            $record[$variable] = $value;
        }

        return $record;
    }
}
