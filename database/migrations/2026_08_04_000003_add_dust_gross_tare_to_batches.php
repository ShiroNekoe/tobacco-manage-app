<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            if (! Schema::hasColumn('batches', 'separation_dust_gross_kg')) {
                $table->decimal('separation_dust_gross_kg', 10, 2)->default(0)->after('separation_dust_kg');
            }
            if (! Schema::hasColumn('batches', 'separation_dust_tare_kg')) {
                $table->decimal('separation_dust_tare_kg', 10, 2)->default(0)->after('separation_dust_gross_kg');
            }
            if (! Schema::hasColumn('batches', 'separation_dust_netto_kg')) {
                $table->decimal('separation_dust_netto_kg', 10, 2)->default(0)->after('separation_dust_tare_kg');
            }
        });

        Schema::table('batch_interim_separations', function (Blueprint $table) {
            if (! Schema::hasColumn('batch_interim_separations', 'separation_dust_gross_kg')) {
                $table->decimal('separation_dust_gross_kg', 10, 2)->default(0)->after('separation_dust_kg');
            }
            if (! Schema::hasColumn('batch_interim_separations', 'separation_dust_tare_kg')) {
                $table->decimal('separation_dust_tare_kg', 10, 2)->default(0)->after('separation_dust_gross_kg');
            }
            if (! Schema::hasColumn('batch_interim_separations', 'separation_dust_netto_kg')) {
                $table->decimal('separation_dust_netto_kg', 10, 2)->default(0)->after('separation_dust_tare_kg');
            }
        });
    }

    public function down(): void
    {
        Schema::table('batch_interim_separations', function (Blueprint $table) {
            $table->dropColumn([
                'separation_dust_gross_kg',
                'separation_dust_tare_kg',
                'separation_dust_netto_kg',
            ]);
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn([
                'separation_dust_gross_kg',
                'separation_dust_tare_kg',
                'separation_dust_netto_kg',
            ]);
        });
    }
};
