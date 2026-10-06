<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ValidationRun extends Model
{
    protected $fillable = [
        'filename','file_hash','source_path','corrected_path','corrected_hash','size_bytes','annex','status',
        'progress','records','processed_records','total_records','rules_loaded','error_count','warning_count',
        'result_count','results','validated_at','started_at','completed_at','error_message','job_id',
    ];

    protected $casts = [
        'size_bytes'=>'integer','progress'=>'integer','records'=>'integer','processed_records'=>'integer',
        'total_records'=>'integer','rules_loaded'=>'integer','error_count'=>'integer','warning_count'=>'integer',
        'result_count'=>'integer','results'=>'array','validated_at'=>'datetime','started_at'=>'datetime',
        'completed_at'=>'datetime',
    ];
}
