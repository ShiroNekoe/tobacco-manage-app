<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dn_shipment_items', function (Blueprint $table) {
            $table->string('material_type')->default('Product')->after('origin_code'); // Product, Bits / Stem, Dust
        });
    }

    public function down(): void
    {
        Schema::table('dn_shipment_items', function (Blueprint $table) {
            $table->dropColumn('material_type');
        });
    }
};
