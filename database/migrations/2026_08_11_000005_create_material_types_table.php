<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('material_types')) {
            Schema::create('material_types', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100);
                $table->string('description', 255)->nullable();
                $table->decimal('default_sack_weight', 8, 2)->default(50.00);
                $table->decimal('default_tare_weight', 8, 2)->default(0.70);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Insert default standard material types
            DB::table('material_types')->insert([
                [
                    'code' => 'Product',
                    'name' => 'Product',
                    'description' => 'Produk Utama Tembakau (Finished Product)',
                    'default_sack_weight' => 50.00,
                    'default_tare_weight' => 0.70,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'Bits / Stem',
                    'name' => 'Bits / Stem',
                    'description' => 'Gagang & Serat Tembakau (Bits / Stem)',
                    'default_sack_weight' => 50.00,
                    'default_tare_weight' => 0.70,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'code' => 'Dust',
                    'name' => 'Dust',
                    'description' => 'Debu Tembakau Halus (Dust)',
                    'default_sack_weight' => 50.00,
                    'default_tare_weight' => 0.70,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_types');
    }
};
