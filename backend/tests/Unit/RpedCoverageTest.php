<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedAdditionalRuleCatalog;
use App\Domain\Res202\Validation\RpedBatch294RuleCatalog;
use App\Domain\Res202\Validation\RpedBatch535RuleCatalog;
use App\Domain\Res202\Validation\RpedBatch600RuleCatalog;
use App\Domain\Res202\Validation\RpedBatch638RuleCatalog;
use App\Domain\Res202\Validation\RpedBatchConsistencySourceRuleCatalog;
use App\Domain\Res202\Validation\RpedBatchCoreSourceRuleCatalog;
use App\Domain\Res202\Validation\RpedRuleCatalog;
use PHPUnit\Framework\TestCase;

final class RpedCoverageTest extends TestCase
{
    public function test_official_catalog_contains_395_unique_rules(): void
    {
        $rows = $this->officialRows();
        $codes = array_column($rows, 'code');
        self::assertCount(395, $codes);
        self::assertCount(395, array_unique($codes));
    }

    public function test_all_executable_rules_are_official_and_unique(): void
    {
        $official = array_fill_keys(array_column($this->officialRows(), 'code'), true);
        $rules = array_merge(
            RpedRuleCatalog::executable(),
            RpedAdditionalRuleCatalog::executable(),
            RpedBatch294RuleCatalog::executable(),
            RpedBatch535RuleCatalog::executable(),
            RpedBatch600RuleCatalog::executable(),
            RpedBatch638RuleCatalog::executable(),
            RpedBatchCoreSourceRuleCatalog::executable(),
            RpedBatchConsistencySourceRuleCatalog::executable(),
        );
        $codes = array_column($rules, 'code');
        $unknown = array_values(array_diff(array_unique($codes), array_keys($official)));
        $duplicates = array_values(array_unique(array_diff_assoc($codes, array_unique($codes))));

        self::assertSame([], $unknown, 'Hay reglas ejecutables que no existen en el catálogo oficial.');
        self::assertSame([], $duplicates, 'Hay códigos de regla duplicados entre los catálogos ejecutables.');
    }

    /** @return array<int,array{code:string}> */
    private function officialRows(): array
    {
        $path = dirname(__DIR__, 3) . '/database/catalog/validation_rules_rped.csv';
        self::assertFileExists($path);
        $handle = fopen($path, 'rb');
        self::assertIsResource($handle);
        $rows = [];
        $header = fgetcsv($handle);
        self::assertIsArray($header);
        $codeIndex = array_search('code', $header, true);
        self::assertNotFalse($codeIndex);
        while (($row = fgetcsv($handle)) !== false) {
            $code = trim((string) ($row[$codeIndex] ?? ''));
            if ($code !== '') {
                $rows[] = ['code' => $code];
            }
        }
        fclose($handle);
        return $rows;
    }
}
