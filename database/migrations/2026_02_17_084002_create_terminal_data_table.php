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
        Schema::create('terminal_data', function (Blueprint $table) {
            $table->id('id_terminal_data');
            $table->unsignedBigInteger('id_terminal_upload');
            
            // Data spesifik terminal - tambah kolom airline
            $table->string('aircraft_id', 20);
            $table->string('airline3_code', 3)->nullable();  // Tambah kolom ini
            $table->unsignedBigInteger('id_airline')->nullable(); // Tambah kolom ini
            $table->string('bandara', 10);
            $table->date('dof');
            $table->string('registrasi', 10);
            $table->string('type', 10);
            $table->string('terminal', 10);
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->string('gate', 10)->nullable();
            $table->string('parking_stand', 10)->nullable();
            $table->decimal('terminal_charge', 15, 2)->nullable();
            
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 10, 4);
            $table->decimal('terminal_charge_idr', 15, 2);            

            $table->string('flight_type', 20)->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('id_terminal_upload')
                ->references('id_terminal_upload')
                ->on('terminal_upload')
                ->onDelete('cascade');

            $table->foreign('id_airline')
                ->references('id')  
                ->on('airlines')     
                ->onDelete('set null');
            
            // Index
            $table->index('id_terminal_upload');
            $table->index('airline3_code');  // Tambah index ini
            $table->index('dof');
            $table->index('aircraft_id');
            $table->index('bandara');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminal_data');
    }
};
