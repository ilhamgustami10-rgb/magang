<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Perbaikan migration enroute_data
    public function up(): void {
        Schema::create('enroute_data', function (Blueprint $table) {
            $table->id('id_enroute_data');
            $table->unsignedBigInteger('id_enroute_upload');
   
            $table->string('aircraft_id', 20);
            $table->string('airline3_code', 3)->nullable(); 
            $table->unsignedBigInteger('id_airline')->nullable(); 
            $table->string('adep', 10);
            $table->string('ades', 10);
            $table->date('dof');
            $table->string('registrasi', 10);
            $table->string('type', 10);
            $table->string('point_in', 10);
            $table->time('time_in')->nullable();
            $table->string('point_out', 10);
            $table->time('time_out');
            $table->decimal('faktor_jarak', 10, 2)->nullable();
            $table->integer('faktor_berat')->nullable();
            $table->decimal('route_unit', 10, 2)->nullable();
            $table->decimal('enroute_charge', 15, 2)->nullable();
            $table->string('currency', 3);
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->decimal('enroute_charge_idr', 15, 2)->nullable();;
            $table->string('flight_type', 20)->nullable();
            
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('id_enroute_upload')
                ->references('id_enroute_upload')
                ->on('enroute_upload')
                ->onDelete('cascade');
            
            $table->foreign('id_airline')
                ->references('id')
                ->on('airlines')
                ->onDelete('set null');
            
            // Index
            $table->index('id_enroute_upload');
            $table->index('airline3_code');
            $table->index('dof');
            $table->index('aircraft_id');
            $table->index(['adep', 'ades']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enroute_data');
    }
};
