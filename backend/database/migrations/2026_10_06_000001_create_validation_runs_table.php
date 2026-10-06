<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('validation_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('filename', 255);
            $table->string('file_hash', 64)->index();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('annex', 20)->default('RPED');
            $table->string('status', 30)->default('COMPLETED')->index();
            $table->unsignedInteger('records')->default(0);
            $table->unsignedInteger('rules_loaded')->default(0);
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedInteger('warning_count')->default(0);
            $table->unsignedInteger('result_count')->default(0);
            $table->json('results')->nullable();
            $table->timestamp('validated_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('validation_runs');
    }
};
