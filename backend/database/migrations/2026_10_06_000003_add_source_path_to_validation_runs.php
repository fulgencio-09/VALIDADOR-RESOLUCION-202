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
            $table->string('source_path', 255)->nullable()->after('file_hash');
        });
    }

    public function down(): void
    {
        Schema::table('validation_runs', function (Blueprint $table): void {
            $table->dropColumn('source_path');
        });
    }
};
