<?php

declare(strict_types=1);

use App\Domain\Res202\Validation\RpedBatch294RuleCatalog;
use App\Domain\Res202\Validation\RuleEngine;

require_once __DIR__ . '/../backend/app/Domain/Res202/Validation/RuleEngine.php';
require_once __DIR__ . '/../backend/app/Domain/Res202/Validation/RpedBatch294RuleCatalog.php';

$path = $argv[1] ?? __DIR__ . '/../440900022701_30092026.txt';
if (!is_file($path)) {
    fwrite(STDERR, "Archivo no encontrado: {$path}\n");
    exit(2);
}

$content = file_get_contents($path);
$lines = preg_split('/\r\n|\n|\r/', (string)$content) ?: [];
$lines = array_values(array_filter($lines, static fn(string $line): bool => $line !== ''));
$control = isset($lines[0]) ? explode('|', $lines[0]) : [];
$cutoff = (($control[0] ?? '') === '1') ? ($control[3] ?? null) : null;

$engine = new RuleEngine();
$rules = RpedBatch294RuleCatalog::executable();
$counts = array_fill_keys(array_column($rules, 'code'), 0);
$records = 0;

foreach ($lines as $lineNumber => $line) {
    $fields = explode('|', $line);
    if (($fields[0] ?? '') !== '2' || count($fields) !== 119) continue;
    $record = array_values($fields);
    $records++;
    foreach ($engine->validate($record, $rules, $cutoff !== null ? ['cutoff_date' => $cutoff] : []) as $result) {
        $counts[$result['code']]++;
    }
}

echo "Archivo: {$path}\n";
echo "Registros tipo 2: {$records}\n";
echo "Fecha de corte: " . ($cutoff ?? 'N/A') . "\n";
echo "Violaciones del lote Error294-Error399:\n";
foreach ($counts as $code => $count) {
    printf("%-12s %d\n", $code, $count);
}

echo "Total de violaciones: " . array_sum($counts) . "\n";
