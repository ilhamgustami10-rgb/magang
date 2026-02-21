<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('airlines', function (Blueprint $table) {
            $table->id();
            $table->string('airline3_code', 3)->unique(); // Contoh: GIA, LNI, CTV
            $table->string('airline_name'); // Contoh: Garuda Indonesia, Lion Air, Citilink
            $table->string('airline_country')->default('Indonesia'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airlines');
    }
};
