<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terminal_data', function (Blueprint $table) {
            $table->id('id_terminal_data');
            $table->unsignedBigInteger('id_terminal_upload');
            
            $table->string('aircraft_id', 20);
            $table->string('airline3_code', 3)->nullable();
            $table->unsignedBigInteger('id_airline')->nullable();
            $table->string('bandara', 10);
            $table->date('tanggal');
            $table->string('registrasi', 10);
            $table->string('type', 10);
            $table->string('terminal', 10);
            $table->time('waktu_kedatangan')->nullable();
            $table->time('waktu_keberangkatan')->nullable();
            $table->string('gate', 10)->nullable();
            $table->string('parking_stand', 10)->nullable();
            $table->decimal('biaya_terminal', 15, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->decimal('biaya_terminal_idr', 15, 2)->nullable();
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->string('status_penerbangan', 20)->nullable();
            
            $table->timestamps();
            
            $table->foreign('id_terminal_upload')
                  ->references('id_terminal_upload')
                  ->on('terminal_upload')
                  ->onDelete('cascade');
                  
            $table->foreign('id_airline')
                  ->references('id')
                  ->on('airlines')
                  ->onDelete('set null');
                  
            $table->index('id_terminal_upload');
            $table->index('airline3_code');
            $table->index('tanggal');
            $table->index('aircraft_id');
            $table->index('bandara');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terminal_data');
    }
};