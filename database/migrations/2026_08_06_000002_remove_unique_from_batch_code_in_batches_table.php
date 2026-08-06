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
        if (Schema::hasIndex('batches', 'batches_batch_code_unique') || Schema::hasIndex('batches', ['batch_code'])) {
            Schema::table('batches', function (Blueprint $table) {
                $table->dropUnique(['batch_code']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasIndex('batches', 'batches_batch_code_unique')) {
            Schema::table('batches', function (Blueprint $table) {
                $table->unique('batch_code');
            });
        }
    }
};
