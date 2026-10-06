<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedRuleCatalog;
use App\Domain\Res202\Validation\RuleEngine;
use PHPUnit\Framework\TestCase;

final class RpedRuleEngineTest extends TestCase
{
    private RuleEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new RuleEngine();
    }

    public function test_realistic_valid_record_passes_the_initial_executable_rules(): void
    {
        $record = $this->record([
            3 => 'CC', 4 => '1234567890', 9 => '1990-01-01', 10 => 'F', 14 => '1',
            29 => '1800-01-01', 30 => '999', 31 => '1800-01-01', 32 => '999',
            102 => '123456789012', 113 => '4', 114 => '21', 115 => '0', 116 => '0', 117 => '21',
        ]);

        self::assertSame([], $this->engine->validate($record, RpedRuleCatalog::executable()));
    }

    public function test_error030_is_detected_when_gestante_requires_female_sex(): void
    {
        $record = $this->record([14 => '1', 10 => 'M']);
        $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable()), 'code');
        self::assertContains('Error030', $codes);
    }

    public function test_error041_and_043_are_detected_for_invalid_measurement_dates(): void
    {
        $record = $this->record([30 => '999', 29 => '2026-09-30', 32 => '999', 31 => '2026-09-30']);
        $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable()), 'code');
        self::assertContains('Error041', $codes);
        self::assertContains('Error043', $codes);
    }

    public function test_allowed_value_rules_are_detected(): void
    {
        $record = $this->record([113 => '9', 114 => '9', 115 => '1', 116 => '1', 117 => '9']);
        $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable()), 'code');
        self::assertContains('Error653', $codes);
        self::assertContains('Error655', $codes);
        self::assertContains('Error656', $codes);
        self::assertContains('Error657', $codes);
        self::assertContains('Error665', $codes);
    }

    public function test_error676_validates_identification_length_by_type(): void
    {
        $valid = $this->record([3 => 'CC', 4 => '1234567890']);
        $invalid = $this->record([3 => 'CC', 4 => '12345678901']);
        $validCodes = array_column($this->engine->validate($valid, RpedRuleCatalog::executable()), 'code');
        $invalidCodes = array_column($this->engine->validate($invalid, RpedRuleCatalog::executable()), 'code');
        self::assertNotContains('Error676', $validCodes);
        self::assertContains('Error676', $invalidCodes);
    }

    public function test_error677_rejects_birth_dates_before_1900(): void
    {
        $record = $this->record([9 => '1845-01-01']);
        $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable()), 'code');
        self::assertContains('Error677', $codes);
    }

    public function test_error678_accepts_only_zero_21_or_twelve_digits(): void
    {
        foreach (['0', '21', '123456789012'] as $value) {
            $record = $this->record([102 => $value]);
            $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable()), 'code');
            self::assertNotContains('Error678', $codes, $value);
        }

        foreach (['1', '123', 'ABCDEFGHIJKL', '12345678901'] as $value) {
            $record = $this->record([102 => $value]);
            $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable()), 'code');
            self::assertContains('Error678', $codes, $value);
        }
    }

    /** @param array<int,string> $overrides */
    private function record(array $overrides = []): array
    {
        $record = array_fill(0, 119, '');
        foreach ($overrides as $variable => $value) {
            $record[$variable] = $value;
        }
        return $record;
    }
}
