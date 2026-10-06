<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Res202\Validation\RpedCatalogLoader;
use App\Domain\Res202\Validation\RpedRuleCatalog;
use App\Domain\Res202\Validation\RpedValidator;
use App\Models\ValidationRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class ValidationController
{
    public function __construct(private readonly RpedValidator $validator) {}

    public function index(): JsonResponse
    {
        $runs = ValidationRun::query()->latest('validated_at')->limit(50)->get([
            'id','filename','file_hash','size_bytes','annex','status','records','rules_loaded',
            'error_count','warning_count','result_count','validated_at','created_at',
        ]);
        return response()->json(['data'=>$runs]);
    }

    public function show(ValidationRun $validationRun): JsonResponse
    {
        return response()->json([
            'id'=>$validationRun->id,'filename'=>$validationRun->filename,'file_hash'=>$validationRun->file_hash,
            'size_bytes'=>$validationRun->size_bytes,'annex'=>$validationRun->annex,'status'=>$validationRun->status,
            'records'=>$validationRun->records,'rules_loaded'=>$validationRun->rules_loaded,
            'error_count'=>$validationRun->error_count,'warning_count'=>$validationRun->warning_count,
            'result_count'=>$validationRun->result_count,'results'=>$validationRun->results ?? [],
            'validated_at'=>$validationRun->validated_at,
        ]);
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
            $result = $this->validator->validate($content,$variables,RpedRuleCatalog::executable());
            $errorCount = count(array_filter($result['results'],static fn(array $item):bool=>strtoupper((string)($item['severity']??'ERROR'))==='ERROR'));
            $warningCount = count(array_filter($result['results'],static fn(array $item):bool=>strtoupper((string)($item['severity']??''))==='WARNING'));
            $rulesLoaded = count(RpedRuleCatalog::executable());
            $filename = basename($uploaded->getClientOriginalName());
            $safeName = preg_replace('/[^A-Za-z0-9._-]/','_', $filename) ?: 'archivo.txt';
            $hash = hash('sha256',$content);
            $sourcePath = 'validation-sources/'.now()->format('Y/m/d').'/'.uniqid('res202_',true).'_'.$safeName;
            Storage::disk('local')->put($sourcePath,$content);
            $run = ValidationRun::create([
                'filename'=>$filename,'file_hash'=>$hash,'source_path'=>$sourcePath,'size_bytes'=>strlen($content),
                'annex'=>'RPED','status'=>'COMPLETED','records'=>$result['records'],'rules_loaded'=>$rulesLoaded,
                'error_count'=>$errorCount,'warning_count'=>$warningCount,'result_count'=>count($result['results']),
                'results'=>$result['results'],'validated_at'=>now(),
            ]);
            return response()->json([
                'id'=>$run->id,'valid'=>$errorCount===0,'filename'=>$filename,'size_bytes'=>strlen($content),'file_hash'=>$hash,
                'records'=>$result['records'],'rules_loaded'=>$rulesLoaded,'error_count'=>$errorCount,'warning_count'=>$warningCount,
                'result_count'=>count($result['results']),'results'=>$result['results'],
            ]);
        } catch (Throwable $exception) {
            report($exception);
            return response()->json(['message'=>'No fue posible procesar el archivo.','detail'=>config('app.debug')?$exception->getMessage():null],500);
        }
    }

    public function health(): JsonResponse
    {
        return response()->json(['service'=>'res202-validator-api','status'=>'ok','version'=>'v1']);
    }
}
