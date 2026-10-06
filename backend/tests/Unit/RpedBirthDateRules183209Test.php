<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedRuleCatalog;
use App\Domain\Res202\Validation\RuleEngine;
use PHPUnit\Framework\TestCase;

final class RpedBirthDateRules183209Test extends TestCase
{
    public function test_rules_183_to_209_are_executable(): void
    {
        $codes = array_column(RpedRuleCatalog::executable(), 'code');
        foreach (['Error183','Error184','Error185','Error186','Error188','Error189','Error190','Error191','Error192','Error193','Error194','Error195','Error196','Error197','Error198','Error199','Error200','Error201','Error202','Error203','Error205','Error207','Error208','Error209'] as $code) {
            self::assertContains($code, $codes);
        }
    }

    public function test_strict_and_inclusive_birth_date_comparisons(): void
    {
        $engine = new RuleEngine();
        $rules = RpedRuleCatalog::executable();
        $record = array_fill(0, 119, '');
        $record[9] = '1990-01-01';
        $record[10] = 'F';
        $record[3] = 'CC';
        $record[4] = '1234567890';
        foreach ([64,65,66,67,69,72,73,75,76,78,80,82,84,87,91,93,96,99,100,103,106,110,111,112] as $variable) {
            $record[$variable] = '1845-01-01';
        }
        $record[64] = '1989-12-31';
        $record[91] = '1990-01-01';
        $results = $engine->validate($record, $rules, ['cutoff_date'=>'2026-09-30']);
        $codes = array_column($results, 'code');
        self::assertContains('Error183', $codes);
        self::assertContains('Error198', $codes);
        self::assertNotContains('Error184', $codes);
    }

    public function test_wildcards_do_not_trigger_birth_date_rules(): void
    {
        $engine = new RuleEngine();
        $rules = RpedRuleCatalog::executable();
        $record = array_fill(0, 119, '');
        $record[9] = '1990-01-01';
        $record[64] = '1800-01-01';
        $codes = array_column($engine->validate($record, $rules, ['cutoff_date'=>'2026-09-30']), 'code');
        self::assertNotContains('Error183', $codes);
    }
}
