<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            if (! Schema::hasColumn('batches', 'product_kg_per_sack')) {
                $table->decimal('product_kg_per_sack', 10, 2)->default(20.00)->after('pack_type');
            }
            if (! Schema::hasColumn('batches', 'separation_product_sack')) {
                $table->integer('separation_product_sack')->default(0)->after('separation_product_kg');
            }
            if (! Schema::hasColumn('batches', 'separation_bits_stem_gross_kg')) {
                $table->decimal('separation_bits_stem_gross_kg', 10, 2)->default(0)->after('separation_bits_stem_kg');
            }
            if (! Schema::hasColumn('batches', 'separation_bits_stem_tare_kg')) {
                $table->decimal('separation_bits_stem_tare_kg', 10, 2)->default(0)->after('separation_bits_stem_gross_kg');
            }
            if (! Schema::hasColumn('batches', 'separation_bits_stem_netto_kg')) {
                $table->decimal('separation_bits_stem_netto_kg', 10, 2)->default(0)->after('separation_bits_stem_tare_kg');
            }
        });

        Schema::table('batch_interim_separations', function (Blueprint $table) {
            if (! Schema::hasColumn('batch_interim_separations', 'separation_product_sack')) {
                $table->integer('separation_product_sack')->default(0)->after('separation_product_kg');
            }
            if (! Schema::hasColumn('batch_interim_separations', 'separation_bits_stem_gross_kg')) {
                $table->decimal('separation_bits_stem_gross_kg', 10, 2)->default(0)->after('separation_bits_stem_kg');
            }
            if (! Schema::hasColumn('batch_interim_separations', 'separation_bits_stem_tare_kg')) {
                $table->decimal('separation_bits_stem_tare_kg', 10, 2)->default(0)->after('separation_bits_stem_gross_kg');
            }
            if (! Schema::hasColumn('batch_interim_separations', 'separation_bits_stem_netto_kg')) {
                $table->decimal('separation_bits_stem_netto_kg', 10, 2)->default(0)->after('separation_bits_stem_tare_kg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('batch_interim_separations', function (Blueprint $table) {
            $table->dropColumn([
                'separation_product_sack',
                'separation_bits_stem_gross_kg',
                'separation_bits_stem_tare_kg',
                'separation_bits_stem_netto_kg',
            ]);
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn([
                'product_kg_per_sack',
                'separation_product_sack',
                'separation_bits_stem_gross_kg',
                'separation_bits_stem_tare_kg',
                'separation_bits_stem_netto_kg',
            ]);
        });
    }
};
