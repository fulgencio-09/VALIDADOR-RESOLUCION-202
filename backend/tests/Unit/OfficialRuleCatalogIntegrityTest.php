<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class OfficialRuleCatalogIntegrityTest extends TestCase
{
    public function test_every_executable_rule_code_belongs_to_the_official_v8_catalog(): void
    {
        $catalogPath = dirname(__DIR__, 3).'/database/catalog/validation_rules_rped.csv';
        self::assertFileExists($catalogPath);

        $official = [];
        $handle = fopen($catalogPath, 'rb');
        self::assertIsResource($handle);
        fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            if (($row[0] ?? '') !== '') {
                $official[] = trim((string)$row[0]);
            }
        }
        fclose($handle);

        $official = array_values(array_unique($official));
        self::assertCount(395, $official, 'El catálogo oficial RPED v8 debe contener exactamente 395 códigos.');

        $validationDir = dirname(__DIR__, 2).'/app/Domain/Res202/Validation';
        $executable = [];
        foreach (glob($validationDir.'/*RuleCatalog.php') ?: [] as $file) {
            $source = (string)file_get_contents($file);
            preg_match_all("/(?:['\"]code['\"]\s*=>\s*['\"]|self::(?:forbidden|afterCutoff|beforeBirth|relation|inValues)\(\s*['\"])([A-Za-z]+\\d{3})['\"])/", $source, $matches);
            foreach ($matches[1] ?? [] as $code) {
                $executable[$code] = true;
            }
        }

        $unknown = array_values(array_diff(array_keys($executable), $official));
        self::assertSame([], $unknown, 'No se permiten reglas de validación que no estén en el catálogo oficial v8.');
    }
}
