<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('traffic_data', function (Blueprint $table) {
            $table->id('id_traffic_data');
            $table->unsignedBigInteger('id_traffic_upload');
            
            // Informasi Penerbangan
            $table->date('tanggal');
            $table->string('aircraft_id', 20);              // ACID
            $table->string('airline3_code', 3)->nullable(); // 3 huruf dari ACID
            $table->unsignedBigInteger('id_airline')->nullable();
            $table->string('registrasi', 20);                // A-REG
            $table->string('type', 20);                      // A-TYPE
            
            // Rute
            $table->string('adep', 10);                       // ADEP
            $table->string('ades', 10);                       // ADES
            
            // Waktu (format string karena bisa kosong)
            $table->string('eobt', 10)->nullable();           // EOBT
            $table->string('pushback', 10)->nullable();       // PUSHBACK
            $table->string('taxi', 10)->nullable();   // TAXI DEP/ARCL
            $table->string('dep_arr_lcl', 10)->nullable();   // TAXI DEP/ARCL
            $table->string('atd', 10)->nullable();            // ATD
            $table->string('eta', 10)->nullable();            // ETA
            $table->string('ata', 10)->nullable();            // ATA
            
            // Ruas / Parking
            $table->string('ruid_dep', 10)->nullable();       // RUID DEP
            $table->string('rui_arr', 10)->nullable();        // RUI ARR
            $table->string('parking_dep', 50)->nullable();    // PARKING DEP
            $table->string('parking_arr', 50)->nullable();    // PARKING ARR
            
            // Informasi Tambahan
            $table->string('pob', 20)->nullable();            // POB (Person On Board)
            $table->string('remark', 255)->nullable();        // REMARK
            $table->string('status_flight', 50)->nullable();  // STATUS FLIGHT
            
            $table->timestamps();
            
            // Foreign Key
            $table->foreign('id_traffic_upload')
                  ->references('id_traffic_upload')
                  ->on('traffic_upload')
                  ->onDelete('cascade');
                  
            $table->foreign('id_airline')
                  ->references('id')
                  ->on('airlines')
                  ->onDelete('set null');
                  
            // Index untuk performa
            $table->index('id_traffic_upload');
            $table->index('airline3_code');
            $table->index('tanggal');
            $table->index('aircraft_id');
            $table->index(['adep', 'ades']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traffic_data');
    }
};