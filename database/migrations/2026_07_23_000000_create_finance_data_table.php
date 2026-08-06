<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_data', function (Blueprint $table) {
            $table->id();
            $table->string('branch')->default('AirNav Juanda (Utama)');
            $table->string('funds_center');
            $table->decimal('rkap', 20, 2)->default(0);
            $table->decimal('release_budget', 20, 2)->default(0);
            $table->decimal('commitment', 20, 2)->default(0);
            $table->decimal('total_consume', 20, 2)->default(0);
            $table->decimal('available_budget', 20, 2)->default(0);
            $table->timestamps();
            $table->index(['branch', 'funds_center']);
        });
    }

    public function down(): void { Schema::dropIfExists('finance_data'); }
};
