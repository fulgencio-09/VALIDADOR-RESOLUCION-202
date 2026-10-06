<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Res202\Validation\RpedCatalogLoader;
use App\Domain\Res202\Validation\RpedRuleCatalog;
use App\Domain\Res202\Validation\RpedValidator;
use App\Jobs\ValidateRpedFile;
use App\Models\ValidationRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

final class ValidationController
{
    public function __construct(private readonly RpedValidator $validator) {}

    public function index(): JsonResponse
    {
        $runs = ValidationRun::query()->latest('created_at')->limit(50)->get([
            'id','filename','file_hash','size_bytes','annex','status','progress','records','processed_records','total_records',
            'rules_loaded','error_count','warning_count','result_count','validated_at','created_at','completed_at',
        ]);
        return response()->json(['data'=>$runs]);
    }

    public function show(ValidationRun $validationRun): JsonResponse
    {
        return response()->json($this->payload($validationRun, true));
    }

    public function status(ValidationRun $validationRun): JsonResponse
    {
        return response()->json($this->payload($validationRun, false));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['file'=>['required','file','max:51200']]);
        $uploaded = $request->file('file');
        if ($uploaded === null || strtolower($uploaded->getClientOriginalExtension()) !== 'txt') {
            return response()->json(['message'=>'Solo se permiten archivos TXT para la validación RPED.','errors'=>['file'=>['El archivo debe tener extensión .txt.']]],422);
        }
        try {
            $content = file_get_contents($uploaded->getRealPath());
            if ($content === false) throw new \RuntimeException('No fue posible leer el archivo cargado.');
            $variables = RpedCatalogLoader::variables(base_path('../database/catalog/variables_rped.csv'));
            if (count($variables) !== 119) throw new \RuntimeException('El catálogo RPED no contiene las 119 variables esperadas.');

            $filename = basename($uploaded->getClientOriginalName());
            $safeName = preg_replace('/[^A-Za-z0-9._-]/','_',$filename) ?: 'archivo.txt';
            $hash = hash('sha256',$content);
            $size = strlen($content);
            $sourcePath = 'validation-sources/'.now()->format('Y/m/d').'/'.uniqid('res202_',true).'_'.$safeName;
            Storage::disk('local')->put($sourcePath,$content);
            $rulesLoaded = count(RpedRuleCatalog::executable());

            if ($size > 5 * 1024 * 1024) {
                $run = ValidationRun::create([
                    'filename'=>$filename,'file_hash'=>$hash,'source_path'=>$sourcePath,'size_bytes'=>$size,
                    'annex'=>'RPED','status'=>'QUEUED','progress'=>0,'rules_loaded'=>$rulesLoaded,
                ]);
                ValidateRpedFile::dispatch($run->id)->onQueue('res202-validation');
                return response()->json(['id'=>$run->id,'valid'=>null,'queued'=>true,'status'=>'QUEUED','filename'=>$filename,
                    'size_bytes'=>$size,'file_hash'=>$hash,'records'=>0,'rules_loaded'=>$rulesLoaded,'error_count'=>0,
                    'warning_count'=>0,'result_count'=>0,'results'=>[]],202);
            }

            $result = $this->validator->validate($content,$variables,RpedRuleCatalog::executable());
            $errorCount = count(array_filter($result['results'], static fn(array $item): bool => strtoupper((string)($item['severity'] ?? 'ERROR')) === 'ERROR'));
            $warningCount = count(array_filter($result['results'], static fn(array $item): bool => strtoupper((string)($item['severity'] ?? '')) === 'WARNING'));
            $run = ValidationRun::create([
                'filename'=>$filename,'file_hash'=>$hash,'source_path'=>$sourcePath,'size_bytes'=>$size,'annex'=>'RPED',
                'status'=>'COMPLETED','progress'=>100,'records'=>$result['records'],'processed_records'=>$result['records'],
                'total_records'=>$result['records'],'rules_loaded'=>$rulesLoaded,'error_count'=>$errorCount,
                'warning_count'=>$warningCount,'result_count'=>count($result['results']),'results'=>$result['results'],
                'validated_at'=>now(),'started_at'=>now(),'completed_at'=>now(),
            ]);
            return response()->json(['id'=>$run->id,'valid'=>$errorCount===0,'queued'=>false,'status'=>'COMPLETED',
                'filename'=>$filename,'size_bytes'=>$size,'file_hash'=>$hash,'records'=>$result['records'],
                'rules_loaded'=>$rulesLoaded,'error_count'=>$errorCount,'warning_count'=>$warningCount,
                'result_count'=>count($result['results']),'results'=>$result['results']]);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json(['message'=>'No fue posible procesar el archivo.','detail'=>config('app.debug')?$exception->getMessage():null],500);
        }
    }

    public function downloadReport(ValidationRun $validationRun): StreamedResponse
    {
        $filename = 'reporte-res202-'.$validationRun->id.'.csv';
        return response()->streamDownload(function () use ($validationRun): void {
            $out = fopen('php://output','wb');
            fputcsv($out,['Código','Severidad','Línea','Variable','Mensaje','Valor'],';');
            foreach (($validationRun->results ?? []) as $item) {
                fputcsv($out,[$item['code']??'', $item['severity']??'', $item['line']??'', $item['variable']??'', $item['message']??'', $item['value']??''],';');
            }
            fclose($out);
        },$filename,['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    public function health(): JsonResponse
    {
        return response()->json(['service'=>'res202-validator-api','status'=>'ok','version'=>'v1']);
    }

    private function payload(ValidationRun $run, bool $includeResults): array
    {
        return ['id'=>$run->id,'filename'=>$run->filename,'file_hash'=>$run->file_hash,'size_bytes'=>$run->size_bytes,
            'annex'=>$run->annex,'status'=>$run->status,'progress'=>$run->progress,'records'=>$run->records,
            'processed_records'=>$run->processed_records,'total_records'=>$run->total_records,'rules_loaded'=>$run->rules_loaded,
            'error_count'=>$run->error_count,'warning_count'=>$run->warning_count,'result_count'=>$run->result_count,
            'results'=>$includeResults ? ($run->results ?? []) : [],'validated_at'=>$run->validated_at,
            'started_at'=>$run->started_at,'completed_at'=>$run->completed_at,'error_message'=>$run->error_message];
    }
}
