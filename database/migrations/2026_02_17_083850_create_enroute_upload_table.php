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
        Schema::create('enroute_upload', function (Blueprint $table) {
            $table->id('id_enroute_upload');
            $table->timestamp('tanggal_jam')->useCurrent();
            $table->string('file_name', 255);
            $table->string('uploaded_by', 50)->nullable();
            $table->date('tanggal_awal')->nullable();  
            $table->date('tanggal_akhir')->nullable();
            $table->enum('status', ['pending', 'processed', 'failed'])->default('processed');
            $table->integer('total_rows')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enroute_upload');
    }
};
