<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pack_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default pack types
        $defaultPackTypes = [
            ['code' => 'Bale', 'name' => 'Bale', 'description' => 'Kemasan Bal Tembakau', 'is_active' => true],
            ['code' => 'Sack', 'name' => 'Sack (Karung)', 'description' => 'Kemasan Sak / Karung', 'is_active' => true],
            ['code' => 'Box', 'name' => 'Box', 'description' => 'Kemasan Box Kayu / Karton', 'is_active' => true],
            ['code' => 'C48', 'name' => 'C48', 'description' => 'Kemasan Box C48', 'is_active' => true],
            ['code' => 'Box/C48', 'name' => 'Box / C48', 'description' => 'Kemasan Box C-48 Khusus', 'is_active' => true],
        ];

        foreach ($defaultPackTypes as $pt) {
            DB::table('pack_types')->insert(array_merge($pt, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pack_types');
    }
};
