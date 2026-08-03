<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('origins', function (Blueprint $table) {
            $table->id();
            $table->string('region_name')->unique();
            $table->timestamps();
        });

        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('dn_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->date('delivery_date');
            $table->string('status')->default('received');
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('delivery_note_id')->constrained('delivery_notes')->onDelete('cascade');
            $table->foreignId('product_type_id')->constrained('product_types')->onDelete('cascade');
            $table->foreignId('origin_id')->constrained('origins')->onDelete('cascade');
            $table->string('pack_type')->default('Bale'); // Bale, Sack, Box
            $table->date('date_of_receipt');

            // 1. Delivery Note (DN) Header
            $table->integer('dn_total_pack')->default(0);
            $table->decimal('dn_gross_weight', 10, 2)->default(0);
            $table->decimal('dn_tare_weight', 10, 2)->default(0);
            $table->decimal('dn_netto_weight', 10, 2)->default(0);

            // 2. Material Receipt List (MRL) Header
            $table->integer('mrl_total_pack')->default(0);
            $table->decimal('mrl_gross_weight', 10, 2)->default(0);
            $table->decimal('mrl_tare_weight', 10, 2)->default(0);
            $table->decimal('mrl_netto_weight', 10, 2)->default(0);
            $table->decimal('discrepancy_dn_vs_mrl_kg', 10, 2)->default(0); // DN Netto - MRL Netto

            // 3. Separation Results (Kg)
            $table->decimal('separation_product_kg', 10, 2)->default(0);
            $table->decimal('separation_bits_stem_kg', 10, 2)->default(0);
            $table->decimal('separation_dust_kg', 10, 2)->default(0);
            $table->decimal('separation_waste_kg', 10, 2)->default(0);

            // Yield Percentages (%)
            $table->decimal('yield_product_pct', 8, 2)->default(0);
            $table->decimal('yield_bits_stem_pct', 8, 2)->default(0);
            $table->decimal('yield_dust_pct', 8, 2)->default(0);
            $table->decimal('yield_waste_pct', 8, 2)->default(0);

            // Workflow & Locking
            $table->string('status')->default('draft'); // draft, locked
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('unlocked_at')->nullable();
            $table->foreignId('unlocked_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('weighing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->integer('sack_number');
            $table->decimal('gross_kg', 10, 2)->default(0);
            $table->decimal('tare_kg', 10, 2)->default(0);
            $table->decimal('netto_kg', 10, 2)->default(0);
            $table->string('remark')->nullable(); // Remnant, Normal
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('weighing_items');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('delivery_notes');
        Schema::dropIfExists('origins');
        Schema::dropIfExists('product_types');
        Schema::dropIfExists('customers');
    }
};
