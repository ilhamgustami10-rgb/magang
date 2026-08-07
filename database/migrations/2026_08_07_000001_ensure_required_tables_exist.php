<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        if (!Schema::hasTable('import_logs')) {
            Schema::create('import_logs', function (Blueprint $table) {
                $table->id();
                $table->string('file_name');
                $table->date('report_date');
                $table->string('source')->default('upload');
                $table->integer('rows_imported')->default(0);
                $table->integer('branches_count')->default(0);
                $table->integer('items_count')->default(0);
                $table->integer('skipped_count')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('budget_realisasi')) {
            Schema::create('budget_realisasi', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('import_id')->nullable();
                $table->date('report_date');
                $table->string('branch_code')->nullable();
                $table->string('branch_name')->nullable();
                $table->string('item_code')->nullable();
                $table->string('item_name')->nullable();
                $table->string('level');
                $table->bigInteger('rkap')->default(0);
                $table->bigInteger('release_budget')->default(0);
                $table->bigInteger('commitment')->default(0);
                $table->bigInteger('total_consume')->default(0);
                $table->bigInteger('available_budget')->default(0);
                $table->timestamps();

                $table->unique(['report_date', 'branch_code', 'item_code', 'level'], 'budget_realisasi_unique_index');

                if (Schema::hasTable('import_logs')) {
                    $table->foreign('import_id')->references('id')->on('import_logs')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        // Safety migration, do not drop anything in down()
    }
};
