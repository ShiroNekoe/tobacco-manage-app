<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('production_runs')) {
            Schema::create('production_runs', function (Blueprint $table) {
                $table->id();
                $table->string('production_code')->nullable();
                $table->unsignedBigInteger('mrl_id')->nullable();
                $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('cascade');
                $table->string('shift')->nullable();
                $table->string('group_name')->nullable();
                $table->string('group_leader_name')->nullable();
                $table->string('operator_1_name')->nullable();
                $table->string('operator_2_name')->nullable();
                $table->timestamp('start_time')->nullable();
                $table->timestamp('finish_time')->nullable();
                $table->decimal('product_weight', 10, 2)->default(0);
                $table->decimal('bits_stem_weight', 10, 2)->default(0);
                $table->decimal('dust_weight', 10, 2)->default(0);
                $table->decimal('waste_weight', 10, 2)->default(0);
                $table->integer('total_downtime_minutes')->default(0);
                $table->decimal('product_yield_pct', 8, 2)->default(0);
                $table->decimal('bits_stem_pct', 8, 2)->default(0);
                $table->decimal('dust_pct', 8, 2)->default(0);
                $table->decimal('waste_pct', 8, 2)->default(0);
                $table->decimal('uptime_hours', 8, 2)->default(0);
                $table->decimal('capacity_kg_hr', 10, 2)->default(0);
                $table->decimal('uptime_pct', 8, 2)->default(0);
                $table->decimal('performance_pct', 8, 2)->default(0);
                $table->string('machine_status')->default('running');
                $table->string('status')->default('running');
                $table->text('remarks')->nullable();
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('locked_at')->nullable();
                $table->timestamp('unlocked_at')->nullable();
                $table->foreignId('unlocked_by_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_runs');
    }
};
