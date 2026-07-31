<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('budget_realisasi', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->string('branch_code')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->string('level'); // 'item', 'cabang', 'total'
            $table->bigInteger('rkap')->default(0);
            $table->bigInteger('release_budget')->default(0);
            $table->bigInteger('commitment')->default(0);
            $table->bigInteger('total_consume')->default(0);
            $table->bigInteger('available_budget')->default(0);
            $table->timestamps();

            // Unique index for upsert logic
            $table->unique(['report_date', 'branch_code', 'item_code', 'level'], 'budget_realisasi_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_realisasi');
    }
};
