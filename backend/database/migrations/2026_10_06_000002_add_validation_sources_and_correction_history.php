<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('validation_runs', function (Blueprint $table): void {
            $table->string('source_path', 500)->nullable()->after('file_hash');
        });

        Schema::create('correction_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('validation_run_id')->constrained('validation_runs')->cascadeOnDelete();
            $table->string('code', 30);
            $table->unsignedInteger('line');
            $table->unsignedTinyInteger('variable');
            $table->string('action', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();
            $table->index(['validation_run_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_history');
        Schema::table('validation_runs', function (Blueprint $table): void {
            $table->dropColumn('source_path');
        });
    }
};
