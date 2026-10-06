<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Res202\Validation\RpedCatalogLoader;
use App\Domain\Res202\Validation\RpedRuleCatalog;
use App\Domain\Res202\Validation\RpedValidator;
use App\Models\ValidationRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class ValidateRpedFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public function __construct(public readonly int $validationRunId) {}

    public function handle(RpedValidator $validator): void
    {
        $run = ValidationRun::findOrFail($this->validationRunId);
        $run->update(['status'=>'PROCESSING','progress'=>5,'started_at'=>now(),'error_message'=>null]);

        try {
            if (!$run->source_path || !Storage::disk('local')->exists($run->source_path)) {
                throw new \RuntimeException('No se encontró el archivo fuente de la validación.');
            }

            $content = Storage::disk('local')->get($run->source_path);
            $lines = preg_split('/\r\n|\n|\r/', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $totalRecords = max(0, count($lines) - 1);
            $run->update(['total_records'=>$totalRecords,'processed_records'=>0,'progress'=>10]);

            $variables = RpedCatalogLoader::variables(base_path('../database/catalog/variables_rped.csv'));
            if (count($variables) !== 119) {
                throw new \RuntimeException('El catálogo RPED no contiene las 119 variables esperadas.');
            }
            $run->update(['progress'=>15]);

            $result = $validator->validate($content, $variables, RpedRuleCatalog::executable());
            $errors = count(array_filter($result['results'], static fn (array $item): bool => strtoupper((string) ($item['severity'] ?? 'ERROR')) === 'ERROR'));
            $warnings = count(array_filter($result['results'], static fn (array $item): bool => strtoupper((string) ($item['severity'] ?? '')) === 'WARNING'));

            $run->update([
                'status'=>'COMPLETED','progress'=>100,'records'=>$result['records'],'processed_records'=>$result['records'],
                'total_records'=>$result['records'],'rules_loaded'=>count(RpedRuleCatalog::executable()),
                'error_count'=>$errors,'warning_count'=>$warnings,'result_count'=>count($result['results']),
                'results'=>$result['results'],'validated_at'=>now(),'completed_at'=>now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $run->update([
                'status'=>'FAILED','progress'=>100,'validated_at'=>now(),'completed_at'=>now(),
                'error_message'=>$exception->getMessage(),'results'=>[[
                    'code'=>'SYSTEM','severity'=>'ERROR','message'=>'No fue posible completar la validación.',
                    'detail'=>config('app.debug') ? $exception->getMessage() : null,
                ]],'result_count'=>1,'error_count'=>1,
            ]);
            throw $exception;
        }
    }
}
