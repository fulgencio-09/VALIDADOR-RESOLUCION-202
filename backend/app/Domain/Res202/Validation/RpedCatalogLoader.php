<?php

declare(strict_types=1);

namespace App\Domain\Res202\Validation;

final class RpedCatalogLoader
{
    /** @return array<int,array{name:string,length:int,type:string}> */
    public static function variables(string $path): array
    {
        if (!is_file($path)) {
            throw new \RuntimeException("No se encontró el catálogo RPED: {$path}");
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException('No fue posible abrir el catálogo RPED.');
        }

        fgetcsv($handle);
        $variables = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5 || !is_numeric($row[0])) {
                continue;
            }
            $number = (int) $row[0];
            $variables[$number] = [
                'name' => trim((string) $row[2]),
                'length' => (int) $row[3],
                'type' => strtoupper(trim((string) $row[4])),
            ];
        }
        fclose($handle);
        ksort($variables);
        return $variables;
    }
}
