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

    public function test_realistic_valid_record_passes_executable_rules(): void
    {
        $record = $this->record();
        $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable(), $this->context(432)), 'code');
        self::assertSame([], $codes);
    }

    public function test_error020_to_096_family_detects_invalid_cases(): void
    {
        $cases = [
            'Error037' => [22=>'4'], 'Error038' => [10=>'M',64=>'2026-01-01'],
            'Error047' => [10=>'M',47=>'6'], 'Error049' => [10=>'M',49=>'2026-01-01'],
            'Error050' => [10=>'M',50=>'2026-01-01'], 'Error051' => [10=>'M',51=>'2026-01-01'],
            'Error063' => [70=>'1'], 'Error064' => [71=>'1'], 'Error069' => [86=>'1'],
            'Error070' => [87=>'2026-01-01'], 'Error071' => [87=>'2026-01-01',10=>'M'],
            'Error072' => [88=>'1'], 'Error073' => [88=>'1',10=>'M'], 'Error074' => [89=>'1'],
            'Error075' => [89=>'1',10=>'M'], 'Error076' => [90=>'999'], 'Error077' => [90=>'999',10=>'M'],
            'Error078' => [91=>'2026-01-01'], 'Error082' => [93=>'2026-01-01'],
            'Error083' => [93=>'2026-01-01',10=>'M'], 'Error084' => [94=>'1'], 'Error085' => [94=>'1',10=>'M'],
            'Error088' => [96=>'2026-01-01'], 'Error089' => [96=>'2026-01-01',10=>'M'],
            'Error090' => [97=>'1'], 'Error091' => [97=>'1',10=>'M'],
            'Error094' => [99=>'2026-01-01',10=>'M'], 'Error095' => [100=>'2026-01-01',10=>'M'],
            'Error096' => [101=>'1',10=>'M'],
        ];
        foreach ($cases as $expectedCode => $overrides) {
            $age = match ($expectedCode) {
                'Error038' => 479, 'Error051' => 7, 'Error063' => 5, 'Error064' => 23,
                'Error069','Error070','Error072','Error074','Error078' => 119,
                'Error076','Error082','Error084' => 120, 'Error088','Error090' => 419,
                default => 432,
            };
            $codes = array_column($this->engine->validate($this->record($overrides), RpedRuleCatalog::executable(), $this->context($age)), 'code');
            self::assertContains($expectedCode, $codes, $expectedCode);
        }
    }

    public function test_date_before_birth_rules_171_to_182_detect_invalid_dates_and_ignore_wildcards(): void
    {
        $variables = [
            'Error171'=>29, 'Error172'=>31, 'Error173'=>49, 'Error174'=>50, 'Error175'=>51,
            'Error176'=>52, 'Error177'=>53, 'Error178'=>55, 'Error179'=>56, 'Error180'=>58,
            'Error181'=>62, 'Error182'=>63,
        ];
        foreach ($variables as $expectedCode => $variable) {
            $record = $this->record([$variable=>'1989-12-31',9=>'1990-01-01']);
            $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable(), $this->context(432)), 'code');
            self::assertContains($expectedCode, $codes, $expectedCode);
        }

        foreach ($variables as $expectedCode => $variable) {
            $record = $this->record([$variable=>'1800-01-01',9=>'1990-01-01']);
            $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable(), $this->context(432)), 'code');
            self::assertNotContains($expectedCode, $codes, $expectedCode.' must ignore wildcard dates');
        }
    }

    public function test_error020_is_detected_when_birth_date_is_missing(): void
    {
        $record = $this->record([9=>'']);
        $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable(), $this->context(432)), 'code');
        self::assertContains('Error020', $codes);
    }

    public function test_existing_allowed_value_rules_remain_active(): void
    {
        $record = $this->record([113=>'9',114=>'9',115=>'1',116=>'1',117=>'9']);
        $codes = array_column($this->engine->validate($record, RpedRuleCatalog::executable(), $this->context(432)), 'code');
        self::assertContains('Error653', $codes); self::assertContains('Error655', $codes);
        self::assertContains('Error656', $codes); self::assertContains('Error657', $codes); self::assertContains('Error665', $codes);
    }

    public function test_error676_validates_identification_length_by_type(): void
    {
        $valid = $this->record([3=>'CC',4=>'1234567890']);
        $invalid = $this->record([3=>'CC',4=>'12345678901']);
        $validCodes = array_column($this->engine->validate($valid, RpedRuleCatalog::executable(), $this->context(432)), 'code');
        $invalidCodes = array_column($this->engine->validate($invalid, RpedRuleCatalog::executable(), $this->context(432)), 'code');
        self::assertNotContains('Error676', $validCodes); self::assertContains('Error676', $invalidCodes);
    }

    public function test_error677_rejects_birth_dates_before_1900(): void
    {
        $codes = array_column($this->engine->validate($this->record([9=>'1845-01-01']), RpedRuleCatalog::executable(), $this->context(432)), 'code');
        self::assertContains('Error677', $codes);
    }

    public function test_error678_accepts_only_zero_21_or_twelve_digits(): void
    {
        foreach (['0','21','123456789012'] as $value) {
            $codes = array_column($this->engine->validate($this->record([102=>$value]), RpedRuleCatalog::executable(), $this->context(432)), 'code');
            self::assertNotContains('Error678', $codes, $value);
        }
        foreach (['1','123','ABCDEFGHIJKL','12345678901'] as $value) {
            $codes = array_column($this->engine->validate($this->record([102=>$value]), RpedRuleCatalog::executable(), $this->context(432)), 'code');
            self::assertContains('Error678', $codes, $value);
        }
    }

    public function test_pending_rules_are_explicitly_separated(): void
    {
        self::assertSame(['Error021','Error022'], array_column(RpedRuleCatalog::pendingExternalCatalog(), 'code'));
        self::assertSame(['Error079'], array_column(RpedRuleCatalog::pendingSourceAmbiguities(), 'code'));
    }

    private function record(array $overrides = []): array
    {
        $record = array_fill(0, 119, '');
        $defaults = [
            3=>'CC', 4=>'1234567890', 9=>'1990-01-01', 10=>'F', 14=>'0', 22=>'0',
            29=>'1800-01-01', 30=>'999', 31=>'1800-01-01', 32=>'999', 47=>'0',
            49=>'1845-01-01', 50=>'1845-01-01', 51=>'1845-01-01', 52=>'1845-01-01', 53=>'1845-01-01',
            55=>'1845-01-01', 56=>'1845-01-01', 58=>'1845-01-01', 62=>'1845-01-01', 63=>'1845-01-01',
            64=>'1845-01-01', 70=>'0', 71=>'0', 72=>'1845-01-01', 80=>'1845-01-01', 82=>'1845-01-01',
            84=>'1845-01-01', 86=>'0', 87=>'1845-01-01', 88=>'0', 89=>'0', 90=>'0', 91=>'1845-01-01',
            93=>'1845-01-01', 94=>'0', 96=>'1845-01-01', 97=>'0', 99=>'1845-01-01', 100=>'1845-01-01',
            101=>'0', 102=>'123456789012', 106=>'1845-01-01', 110=>'1845-01-01', 111=>'1845-01-01',
            112=>'1845-01-01', 113=>'4', 114=>'0', 115=>'0', 116=>'0', 117=>'0',
        ];
        foreach ($defaults as $variable=>$value) $record[$variable] = $value;
        foreach ($overrides as $variable=>$value) $record[$variable] = $value;
        return $record;
    }

    private function context(int $ageMonths): array
    {
        return ['cutoff_date'=>'2026-01-01','age_months'=>$ageMonths,'age_years'=>intdiv($ageMonths,12),'age_days'=>$ageMonths*30];
    }
}
