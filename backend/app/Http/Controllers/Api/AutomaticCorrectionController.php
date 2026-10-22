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
            $error536 = [];

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

                // Error536 es estrictamente unidireccional: si variable 36 = 0,
                // debe pasar a 21. No se debe usar una operación toggle porque
                // los cuatro valores posibles del contexto podrían deshacer la
                // corrección cuando existan otros hallazgos en la misma línea.
                if ($code === 'Error536' && $variable === 36 && $value === '0') {
                    $error536[] = ['code' => $code, 'line' => $line, 'variable' => 36];
                    continue;
                }

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

            if ($corrections === [] && $error536 === []) {
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

            // Aplicar Error536 directamente sobre el archivo fuente para
            // garantizar 0 -> 21, sin posibilidad de invertir el valor.
            foreach ($error536 as $item) {
                $lines = preg_split('/\r\n|\n|\r/', $source) ?: [];
                $index = $item['line'] - 1;
                if (!isset($lines[$index])) {
                    continue;
                }
                $fields = explode('|', $lines[$index]);
                if (!array_key_exists(36, $fields)) {
                    continue;
                }
                if ($fields[36] === '0') {
                    $fields[36] = '21';
                    $source = implode("\r\n", $lines);
                    $changes536[] = [
                        'code' => 'Error536',
                        'line' => $item['line'],
                        'variable' => 36,
                        'action' => 'set_variable_36_21',
                        'old_value' => '0',
                        'new_value' => '21',
                    ];
                }
            }

            $result = $this->service->apply($source, $corrections);
            $changes = array_merge($changes536 ?? [], $result['changes']);
            $correctedContent = $result['content'];

            $variables = RpedCatalogLoader::variables(base_path('../database/catalog/variables_rped.csv'));
            $verification = $this->validator->validate($correctedContent, $variables, RpedRuleCatalog::executable());

            $filename = pathinfo($validationRun->filename, PATHINFO_FILENAME) . '_CORREGIDO.txt';
            $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: 'archivo_CORREGIDO.txt';
            $path = 'validation-corrected/' . now()->format('Y/m/d') . '/' . uniqid('res202_auto_', true) . '_' . $safeName;
            Storage::disk('local')->put($path, $correctedContent);
            $correctedHash = hash('sha256', $correctedContent);

            DB::transaction(function () use ($validationRun, $changes, $path, $correctedHash, $verification): void {
                foreach ($changes as $change) {
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
                    'status' => 'COMPLETED',
                    'progress' => 100,
                    'records' => $verification['records'],
                    'processed_records' => $verification['records'],
                    'total_records' => $verification['records'],
                    'error_count' => $verification['errors'],
                    'warning_count' => $verification['warnings'],
                    'result_count' => count($verification['results']),
                    'results' => $verification['results'],
                    'validated_at' => now(),
                    'completed_at' => now(),
                ])->save();
            });

            return response()->json([
                'message' => 'Correcciones automáticas aplicadas y archivo revalidado correctamente.',
                'filename' => $filename,
                'changes' => $changes,
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
