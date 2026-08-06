<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('batches')) {
            Schema::table('batches', function (Blueprint $table) {
                if (! Schema::hasColumn('batches', 'separation_p1_data')) {
                    $table->json('separation_p1_data')->nullable()->after('dust_items');
                }
                if (! Schema::hasColumn('batches', 'separation_p2_data')) {
                    $table->json('separation_p2_data')->nullable()->after('separation_p1_data');
                }
            });
        }

        if (Schema::hasTable('batch_interim_separations')) {
            Schema::table('batch_interim_separations', function (Blueprint $table) {
                if (! Schema::hasColumn('batch_interim_separations', 'separation_p1_data')) {
                    $table->json('separation_p1_data')->nullable()->after('dust_items');
                }
                if (! Schema::hasColumn('batch_interim_separations', 'separation_p2_data')) {
                    $table->json('separation_p2_data')->nullable()->after('separation_p1_data');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('batches')) {
            Schema::table('batches', function (Blueprint $table) {
                if (Schema::hasColumn('batches', 'separation_p1_data')) {
                    $table->dropColumn('separation_p1_data');
                }
                if (Schema::hasColumn('batches', 'separation_p2_data')) {
                    $table->dropColumn('separation_p2_data');
                }
            });
        }

        if (Schema::hasTable('batch_interim_separations')) {
            Schema::table('batch_interim_separations', function (Blueprint $table) {
                if (Schema::hasColumn('batch_interim_separations', 'separation_p1_data')) {
                    $table->dropColumn('separation_p1_data');
                }
                if (Schema::hasColumn('batch_interim_separations', 'separation_p2_data')) {
                    $table->dropColumn('separation_p2_data');
                }
            });
        }
    }
};
