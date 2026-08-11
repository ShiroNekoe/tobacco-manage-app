<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dn_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('dn_number')->unique();
            $table->date('shipment_date');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('product_type_id')->nullable()->constrained('product_types')->nullOnDelete();
            $table->string('vehicle_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('destination')->nullable();
            $table->text('notes')->nullable();
            $table->integer('total_sacks')->default(0);
            $table->decimal('total_gross_kg', 12, 2)->default(0);
            $table->decimal('total_tare_kg', 12, 2)->default(0);
            $table->decimal('total_netto_kg', 12, 2)->default(0);
            $table->string('status')->default('Shipped'); // Draft, Shipped, Delivered, Approved
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('dn_shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dn_shipment_id')->constrained('dn_shipments')->cascadeOnDelete();
            $table->integer('item_no')->default(1);
            $table->string('origin');
            $table->string('origin_code');
            $table->integer('standard_sack_count')->default(0);
            $table->decimal('standard_gross_per_sack', 10, 2)->default(50.70);
            $table->decimal('standard_tare_per_sack', 10, 2)->default(0.70);
            $table->decimal('standard_netto_per_sack', 10, 2)->default(50.00);
            $table->boolean('has_remnant')->default(false);
            $table->decimal('remnant_gross_kg', 10, 2)->default(0);
            $table->decimal('remnant_tare_kg', 10, 2)->default(0);
            $table->decimal('remnant_netto_kg', 10, 2)->default(0);
            $table->integer('total_sacks')->default(0);
            $table->decimal('total_gross_kg', 12, 2)->default(0);
            $table->decimal('total_tare_kg', 12, 2)->default(0);
            $table->decimal('total_netto_kg', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dn_shipment_items');
        Schema::dropIfExists('dn_shipments');
    }
};
