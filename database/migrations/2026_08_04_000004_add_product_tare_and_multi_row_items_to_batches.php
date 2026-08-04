<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            if (! Schema::hasColumn('batches', 'product_tare_per_sack')) {
                $table->decimal('product_tare_per_sack', 10, 2)->default(2.00)->after('product_kg_per_sack');
            }
            if (! Schema::hasColumn('batches', 'separation_product_gross_kg')) {
                $table->decimal('separation_product_gross_kg', 10, 2)->default(0)->after('separation_product_sack');
            }
            if (! Schema::hasColumn('batches', 'separation_product_tare_kg')) {
                $table->decimal('separation_product_tare_kg', 10, 2)->default(0)->after('separation_product_gross_kg');
            }
            if (! Schema::hasColumn('batches', 'bit_stem_items')) {
                $table->json('bit_stem_items')->nullable()->after('separation_bits_stem_netto_kg');
            }
            if (! Schema::hasColumn('batches', 'dust_items')) {
                $table->json('dust_items')->nullable()->after('separation_dust_netto_kg');
            }
        });

        Schema::table('batch_interim_separations', function (Blueprint $table) {
            if (! Schema::hasColumn('batch_interim_separations', 'product_tare_per_sack')) {
                $table->decimal('product_tare_per_sack', 10, 2)->default(2.00)->after('user_id');
            }
            if (! Schema::hasColumn('batch_interim_separations', 'separation_product_gross_kg')) {
                $table->decimal('separation_product_gross_kg', 10, 2)->default(0)->after('separation_product_sack');
            }
            if (! Schema::hasColumn('batch_interim_separations', 'separation_product_tare_kg')) {
                $table->decimal('separation_product_tare_kg', 10, 2)->default(0)->after('separation_product_gross_kg');
            }
            if (! Schema::hasColumn('batch_interim_separations', 'bit_stem_items')) {
                $table->json('bit_stem_items')->nullable()->after('separation_bits_stem_netto_kg');
            }
            if (! Schema::hasColumn('batch_interim_separations', 'dust_items')) {
                $table->json('dust_items')->nullable()->after('separation_dust_netto_kg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('batch_interim_separations', function (Blueprint $table) {
            $table->dropColumn([
                'product_tare_per_sack',
                'separation_product_gross_kg',
                'separation_product_tare_kg',
                'bit_stem_items',
                'dust_items',
            ]);
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn([
                'product_tare_per_sack',
                'separation_product_gross_kg',
                'separation_product_tare_kg',
                'bit_stem_items',
                'dust_items',
            ]);
        });
    }
};
