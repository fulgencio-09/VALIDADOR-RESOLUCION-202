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
            $table->unsignedTinyInteger('progress')->default(0)->after('status');
            $table->unsignedInteger('processed_records')->default(0)->after('records');
            $table->unsignedInteger('total_records')->default(0)->after('processed_records');
            $table->timestamp('started_at')->nullable()->after('validated_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->text('error_message')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('validation_runs', function (Blueprint $table): void {
            $table->dropColumn([
                'progress','processed_records','total_records','started_at','completed_at','error_message',
            ]);
        });
    }
};
