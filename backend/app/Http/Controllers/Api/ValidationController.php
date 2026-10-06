<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Res202\Validation\RpedCatalogLoader;
use App\Domain\Res202\Validation\RpedRuleCatalog;
use App\Domain\Res202\Validation\RpedValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

final class ValidationController
{
    public function __construct(private readonly RpedValidator $validator) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'],
        ]);

        $uploaded = $request->file('file');
        if ($uploaded === null || strtolower($uploaded->getClientOriginalExtension()) !== 'txt') {
            return response()->json([
                'message' => 'Solo se permiten archivos TXT para la validación RPED.',
                'errors' => ['file' => ['El archivo debe tener extensión .txt.']],
            ], 422);
        }

        try {
            $content = file_get_contents($uploaded->getRealPath());
            if ($content === false) {
                throw new \RuntimeException('No fue posible leer el archivo cargado.');
            }

            $variables = RpedCatalogLoader::variables(base_path('../database/catalog/variables_rped.csv'));
            if (count($variables) !== 119) {
                throw new \RuntimeException('El catálogo RPED no contiene las 119 variables esperadas.');
            }

            $result = $this->validator->validate(
                $content,
                $variables,
                RpedRuleCatalog::executable(),
            );

            $errorCount = count(array_filter(
                $result['results'],
                static fn (array $item): bool => strtoupper((string) ($item['severity'] ?? 'ERROR')) === 'ERROR',
            ));
            $warningCount = count(array_filter(
                $result['results'],
                static fn (array $item): bool => strtoupper((string) ($item['severity'] ?? '')) === 'WARNING',
            ));

            return response()->json([
                'valid' => $errorCount === 0,
                'filename' => $uploaded->getClientOriginalName(),
                'size_bytes' => $uploaded->getSize(),
                'records' => $result['records'],
                'rules_loaded' => count(RpedRuleCatalog::executable()),
                'error_count' => $errorCount,
                'warning_count' => $warningCount,
                'result_count' => count($result['results']),
                'results' => $result['results'],
            ]);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json([
                'message' => 'No fue posible procesar el archivo.',
                'detail' => config('app.debug') ? $exception->getMessage() : null,
            ], 500);
        }
    }

    public function health(): JsonResponse
    {
        return response()->json([
            'service' => 'res202-validator-api',
            'status' => 'ok',
            'version' => 'v1',
        ]);
    }
}
