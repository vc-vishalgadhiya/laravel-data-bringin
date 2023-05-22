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
            $table->integer('total_count');
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->string('file_name');
            $table->string('file_path');
            $table->string('failed_file_path')->nullable();
            $table->string('batch_id')->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
