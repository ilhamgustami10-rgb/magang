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
        Schema::table('budget_realisasi', function (Blueprint $table) {
            $table->bigInteger('rkap')->change();
            $table->bigInteger('release_budget')->change();
            $table->bigInteger('commitment')->change();
            $table->bigInteger('total_consume')->change();
            $table->bigInteger('available_budget')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_realisasi', function (Blueprint $table) {
            $table->integer('rkap')->change();
            $table->integer('release_budget')->change();
            $table->integer('commitment')->change();
            $table->integer('total_consume')->change();
            $table->integer('available_budget')->change();
        });
    }
};
