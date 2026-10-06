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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class CorrectionController
{
    public function __construct(private readonly RpedCorrectionService $service, private readonly RpedValidator $validator) {}

    public function catalog(): JsonResponse
    {
        return response()->json(['data'=>RpedCorrectionCatalog::available()]);
    }

    public function apply(Request $request, ValidationRun $validationRun): JsonResponse
    {
        $validated = $request->validate([
            'corrections'=>['required','array','min:1'],
            'corrections.*.code'=>['required','string','max:30'],
            'corrections.*.line'=>['required','integer','min:1'],
            'corrections.*.variable'=>['required','integer','between:0,118'],
            'corrections.*.action'=>['required','string','max:80'],
        ]);
        try {
            if (!$validationRun->source_path || !Storage::disk('local')->exists($validationRun->source_path)) {
                return response()->json(['message'=>'El archivo fuente de esta validación no está disponible para corrección.'],409);
            }
            $source = Storage::disk('local')->get($validationRun->source_path);
            $result = $this->service->apply($source,$validated['corrections']);
            $variables = RpedCatalogLoader::variables(base_path('../database/catalog/variables_rped.csv'));
            $verification = $this->validator->validate($result['content'],$variables,RpedRuleCatalog::executable());
            $filename = pathinfo($validationRun->filename,PATHINFO_FILENAME).'_CORREGIDO.txt';
            $safeName = preg_replace('/[^A-Za-z0-9._-]/','_',$filename) ?: 'archivo_CORREGIDO.txt';
            $path = 'validation-corrected/'.now()->format('Y/m/d').'/'.uniqid('res202_',true).'_'.$safeName;
            Storage::disk('local')->put($path,$result['content']);
            DB::transaction(function () use ($validationRun,$result): void {
                foreach ($result['changes'] as $change) {
                    DB::table('correction_history')->insert([
                        'validation_run_id'=>$validationRun->id,'code'=>$change['code'],'line'=>$change['line'],
                        'variable'=>$change['variable'],'action'=>$change['action'],'old_value'=>$change['old_value'],
                        'new_value'=>$change['new_value'],'created_at'=>now(),
                    ]);
                }
            });
            return response()->json([
                'message'=>'Corrección generada y revalidada correctamente.','filename'=>$filename,
                'changes'=>$result['changes'],'verification'=>[
                    'valid'=>$verification['valid'],'records'=>$verification['records'],
                    'error_count'=>$verification['errors'],'warning_count'=>$verification['warnings'],
                    'result_count'=>count($verification['results']),
                ],
                'download_url'=>url('/api/validations/'.$validationRun->id.'/corrected-download?path='.urlencode($path)),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json(['message'=>'No fue posible generar el archivo corregido.','detail'=>config('app.debug')?$exception->getMessage():null],422);
        }
    }

    public function download(ValidationRun $validationRun, Request $request)
    {
        $path = (string)$request->query('path','');
        if (!str_starts_with($path,'validation-corrected/') || !Storage::disk('local')->exists($path)) abort(404);
        return Storage::disk('local')->download($path,pathinfo($path,PATHINFO_BASENAME),['Content-Type'=>'text/plain; charset=utf-8']);
    }
}
