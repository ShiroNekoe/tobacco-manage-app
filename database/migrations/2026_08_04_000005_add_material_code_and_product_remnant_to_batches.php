<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->string('material_code')->nullable()->after('origin_id');
            $table->decimal('separation_product_remnant_gross_kg', 10, 2)->default(0)->after('separation_product_sack');
            $table->decimal('separation_product_remnant_tare_kg', 10, 2)->default(0)->after('separation_product_remnant_gross_kg');
            $table->decimal('separation_product_remnant_kg', 10, 2)->default(0)->after('separation_product_remnant_tare_kg');
        });

        Schema::table('batch_interim_separations', function (Blueprint $table) {
            $table->decimal('separation_product_remnant_gross_kg', 10, 2)->default(0)->after('separation_product_sack');
            $table->decimal('separation_product_remnant_tare_kg', 10, 2)->default(0)->after('separation_product_remnant_gross_kg');
            $table->decimal('separation_product_remnant_kg', 10, 2)->default(0)->after('separation_product_remnant_tare_kg');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn([
                'material_code',
                'separation_product_remnant_gross_kg',
                'separation_product_remnant_tare_kg',
                'separation_product_remnant_kg',
            ]);
        });

        Schema::table('batch_interim_separations', function (Blueprint $table) {
            $table->dropColumn([
                'separation_product_remnant_gross_kg',
                'separation_product_remnant_tare_kg',
                'separation_product_remnant_kg',
            ]);
        });
    }
};
