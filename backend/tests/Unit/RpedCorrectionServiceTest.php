<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Res202\Correction\RpedCorrectionService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class RpedCorrectionServiceTest extends TestCase
{
    public function test_scientific_notation_is_converted_exactly(): void
    {
        $result = (new RpedCorrectionService())->apply("1|EPSS41|2026-09-30|2026-09-30|1\r\n2|1|440900022701|CC|2.60874E+13", [
            ['code'=>'Error220','line'=>2,'variable'=>4,'action'=>'scientific_notation_to_integer'],
        ]);
        self::assertSame('26087400000000', explode('|', explode("\r\n", $result['content'])[1])[4]);
        self::assertSame('2.60874E+13', $result['changes'][0]['old_value']);
    }

    public function test_unsupported_notation_is_rejected(): void
    {
        $this->expectException(RuntimeException::class);
        (new RpedCorrectionService())->apply('2|1|x|x|1.25E+1.2', [
            ['code'=>'Error220','line'=>1,'variable'=>4,'action'=>'scientific_notation_to_integer'],
        ]);
    }
}
