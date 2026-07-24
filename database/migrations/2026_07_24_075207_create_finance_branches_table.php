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
        Schema::create('finance_branches', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->bigInteger('rkap')->default(0);
            $table->bigInteger('release_budget')->default(0);
            $table->bigInteger('commitment')->default(0);
            $table->bigInteger('total_consume')->default(0);
            $table->bigInteger('available_budget')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_branches');
    }
};
