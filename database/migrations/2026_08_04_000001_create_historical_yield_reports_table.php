<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historical_yield_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type'); // 'by_product' or 'avg'
            $table->integer('row_number')->nullable();
            $table->string('product')->nullable();
            $table->string('origin')->nullable();
            $table->string('metric_category')->nullable(); // Yield (Kg), Bits Stem (Kg), Dust (Kg), Waste (Kg), Yield %, etc.
            $table->json('batch_data')->nullable(); // JSON object storing batch 1 to 25 values
            $table->decimal('total_qty', 12, 2)->nullable();
            $table->decimal('avg_pct', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_yield_reports');
    }
};
