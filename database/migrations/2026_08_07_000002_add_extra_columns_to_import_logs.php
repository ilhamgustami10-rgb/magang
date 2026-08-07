<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('import_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('import_logs', 'duration_seconds')) {
                $table->float('duration_seconds')->nullable()->default(null);
            }
            if (!Schema::hasColumn('import_logs', 'failed_count')) {
                $table->integer('failed_count')->default(0);
            }
            if (!Schema::hasColumn('import_logs', 'skipped_count')) {
                $table->integer('skipped_count')->default(0);
            }
            if (!Schema::hasColumn('import_logs', 'status')) {
                $table->string('status', 20)->default('success');
            }
        });
    }

    public function down(): void
    {
        Schema::table('import_logs', function (Blueprint $table) {
            if (Schema::hasColumn('import_logs', 'duration_seconds')) {
                $table->dropColumn('duration_seconds');
            }
            if (Schema::hasColumn('import_logs', 'failed_count')) {
                $table->dropColumn('failed_count');
            }
            if (Schema::hasColumn('import_logs', 'status')) {
                $table->dropColumn('status');
            }
            // skipped_count should probably not be dropped as it may have existed before, but matching instructions
        });
    }
};
