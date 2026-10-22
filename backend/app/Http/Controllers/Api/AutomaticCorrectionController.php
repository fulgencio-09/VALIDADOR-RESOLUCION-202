<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Res202\Correction\RpedCorrectionCatalog;
use App\Domain\Res202\Correction\RpedCorrectionService;
use App\Domain\Res202\Validation\RpedCatalogLoader;
use App\Domain\Res202\Validation\RpedRuleCatalog;
use App\Domain\Res202\Validation\RpedValidator;
use App\Models\ValidationRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class AutomaticCorrectionController
{
    public function __construct(
        private readonly RpedCorrectionService $service,
        private readonly RpedValidator $validator,
    ) {}

    public function apply(ValidationRun $validationRun): JsonResponse
    {
        try {
            if (!$validationRun->source_path || !Storage::disk('local')->exists($validationRun->source_path)) {
                return response()->json([
                    'message' => 'El archivo fuente de esta validación no está disponible para corrección.',
                ], 409);
            }

            $corrections = [];
            $seen = [];

            foreach (($validationRun->results ?? []) as $item) {
                $code = (string) ($item['code'] ?? '');
                $catalog = RpedCorrectionCatalog::forCode($code);

                if (($catalog['automatic'] ?? false) !== true) {
                    continue;
                }

                $line = (int) ($item['line'] ?? 0);
                $variable = (int) ($item['variable'] ?? -1);
                $action = (string) ($catalog['action'] ?? '');
                $value = (string) ($item['value'] ?? '');

                if ($line < 1 || $variable < 0 || $variable > 118 || $action === '') {
                    continue;
                }

                if ($action === 'scientific_notation_to_integer' && !preg_match('/^[0-9]+(?:\.[0-9]+)?[eE]\+?[0-9]+$/', $value)) {
                    continue;
                }

                // Dos errores distintos pueden apuntar a la misma variable y usar
                // exactamente la misma acción (por ejemplo Error082 y Error621
                // sobre la variable 93). Una corrección bidireccional no debe
                // ejecutarse dos veces porque la segunda ejecución desharía la primera.
                $key = $line . '|' . $variable . '|' . $action;
                if (isset($seen[$key])) {
                    continue;
                }

                $seen[$key] = true;
                $corrections[] = [
                    'code' => $code,
                    'line' => $line,
                    'variable' => $variable,
                    'action' => $action,
                ];
            }

            if ($corrections === []) {
                return response()->json([
                    'message' => 'No se encontraron correcciones automáticas seguras para esta validación.',
                    'changes' => [],
                    'verification' => [
                        'valid' => (int) $validationRun->error_count === 0,
                        'records' => (int) $validationRun->records,
                        'error_count' => (int) $validationRun->error_count,
                        'warning_count' => (int) $validationRun->warning_count,
                        'result_count' => (int) $validationRun->result_count,
                    ],
                ]);
            }

            $source = Storage::disk('local')->get($validationRun->source_path);
            $result = $this->service->apply($source, $corrections);
            $variables = RpedCatalogLoader::variables(base_path('../database/catalog/variables_rped.csv'));
            $verification = $this->validator->validate($result['content'], $variables, RpedRuleCatalog::executable());

            $filename = pathinfo($validationRun->filename, PATHINFO_FILENAME) . '_CORREGIDO.txt';
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'archivo_CORREGIDO.txt';
            $path = 'validation-corrected/' . now()->format('Y/m/d') . '/' . uniqid('res202_auto_', true) . '_' . $safeName;
            Storage::disk('local')->put($path, $result['content']);
            $correctedHash = hash('sha256', $result['content']);

            DB::transaction(function () use ($validationRun, $result, $path, $correctedHash): void {
                foreach ($result['changes'] as $change) {
                    DB::table('correction_history')->insert([
                        'validation_run_id' => $validationRun->id,
                        'code' => $change['code'],
                        'line' => $change['line'],
                        'variable' => $change['variable'],
                        'action' => $change['action'],
                        'old_value' => $change['old_value'],
                        'new_value' => $change['new_value'],
                        'created_at' => now(),
                    ]);
                }

                $validationRun->forceFill([
                    'corrected_path' => $path,
                    'corrected_hash' => $correctedHash,
                ])->save();
            });

            return response()->json([
                'message' => 'Correcciones automáticas aplicadas y archivo revalidado correctamente.',
                'filename' => $filename,
                'changes' => $result['changes'],
                'verification' => [
                    'valid' => $verification['valid'],
                    'records' => $verification['records'],
                    'error_count' => $verification['errors'],
                    'warning_count' => $verification['warnings'],
                    'result_count' => count($verification['results']),
                ],
                'download_url' => url('/api/validations/' . $validationRun->id . '/corrected-download'),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'No fue posible ejecutar las correcciones automáticas.',
                'detail' => config('app.debug') ? $exception->getMessage() : null,
            ], 422);
        }
    }
}
