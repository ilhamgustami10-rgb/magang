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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->date('report_date');
            $table->string('source')->default('upload'); // upload, folder, schedule
            $table->integer('rows_imported')->default(0);
            $table->integer('branches_count')->default(0);
            $table->integer('items_count')->default(0);
            $table->integer('skipped_count')->default(0);
            $table->timestamps();
        });

        Schema::table('budget_realisasi', function (Blueprint $table) {
            $table->unsignedBigInteger('import_id')->nullable()->after('id');
            // We can add foreign key, but it's optional and might complicate cascade if deleted.
            // Let's add cascade delete
            $table->foreign('import_id')->references('id')->on('import_logs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_realisasi', function (Blueprint $table) {
            $table->dropForeign(['import_id']);
            $table->dropColumn('import_id');
        });
        Schema::dropIfExists('import_logs');
    }
};
