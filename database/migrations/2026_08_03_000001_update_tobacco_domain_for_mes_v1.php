<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ensure role field supports all roles
        });

        Schema::table('batches', function (Blueprint $table) {
            if (! Schema::hasColumn('batches', 'discrepancy_remark')) {
                $table->text('discrepancy_remark')->nullable()->after('discrepancy_dn_vs_mrl_kg');
            }
            if (! Schema::hasColumn('batches', 'force_close_reason')) {
                $table->text('force_close_reason')->nullable()->after('discrepancy_remark');
            }
            if (! Schema::hasColumn('batches', 'mrl_discrepancy_flag')) {
                $table->boolean('mrl_discrepancy_flag')->default(false)->after('force_close_reason');
            }
            if (! Schema::hasColumn('batches', 'mrl_approved_at')) {
                $table->timestamp('mrl_approved_at')->nullable()->after('mrl_discrepancy_flag');
            }
            if (! Schema::hasColumn('batches', 'mrl_approved_by_user_id')) {
                $table->foreignId('mrl_approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            }
        });

        // Create batch_origins pivot table for multi-origin support
        if (! Schema::hasTable('batch_origins')) {
            Schema::create('batch_origins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
                $table->foreignId('origin_id')->constrained('origins')->onDelete('cascade');
                $table->decimal('allocated_kg', 10, 2)->default(0);
                $table->decimal('processed_kg', 10, 2)->default(0);
                $table->decimal('remaining_kg', 10, 2)->default(0);
                $table->string('status')->default('active'); // active, completed
                $table->timestamps();
            });
        }

        // Create downtime_events table if not exists
        if (! Schema::hasTable('downtime_events')) {
            Schema::create('downtime_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('production_run_id')->nullable();
                $table->foreignId('batch_id')->nullable()->constrained('batches')->onDelete('cascade');
                $table->integer('downtime_minutes')->default(0);
                $table->enum('reason', [
                    'Machine Breakdown',
                    'Material Shortage',
                    'Scheduled Maintenance',
                    'Quality Hold',
                    'Operator Break',
                    'Other'
                ])->default('Other');
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('downtime_events');
        Schema::dropIfExists('batch_origins');

        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['mrl_approved_by_user_id']);
            $table->dropColumn([
                'discrepancy_remark',
                'force_close_reason',
                'mrl_discrepancy_flag',
                'mrl_approved_at',
                'mrl_approved_by_user_id'
            ]);
        });
    }
};
