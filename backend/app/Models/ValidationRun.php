<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class ValidationRun extends Model
{
    protected $fillable = [
        'filename',
        'file_hash',
        'size_bytes',
        'annex',
        'status',
        'records',
        'rules_loaded',
        'error_count',
        'warning_count',
        'result_count',
        'results',
        'validated_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'records' => 'integer',
        'rules_loaded' => 'integer',
        'error_count' => 'integer',
        'warning_count' => 'integer',
        'result_count' => 'integer',
        'results' => 'array',
        'validated_at' => 'datetime',
    ];
}
