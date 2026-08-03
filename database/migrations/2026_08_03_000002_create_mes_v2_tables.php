<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add Shift, Group, and Customer assignment to users
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'shift')) {
                $table->string('shift')->nullable()->after('role'); // Shift 1, Shift 2
            }
            if (! Schema::hasColumn('users', 'group')) {
                $table->string('group')->nullable()->after('shift'); // Group A, Group B, Group C
            }
            if (! Schema::hasColumn('users', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('group')->constrained('customers')->onDelete('set null');
            }
        });

        // 2. Add Supervisor Approval & Custom Remarks to batches
        Schema::table('batches', function (Blueprint $table) {
            if (! Schema::hasColumn('batches', 'supervisor_approval_status')) {
                $table->string('supervisor_approval_status')->default('PENDING')->after('status'); // PENDING, APPROVED, REJECTED
            }
            if (! Schema::hasColumn('batches', 'supervisor_approved_at')) {
                $table->timestamp('supervisor_approved_at')->nullable()->after('supervisor_approval_status');
            }
            if (! Schema::hasColumn('batches', 'supervisor_approved_by_user_id')) {
                $table->foreignId('supervisor_approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            }
            if (! Schema::hasColumn('batches', 'custom_dn_remark')) {
                $table->text('custom_dn_remark')->nullable();
            }
            if (! Schema::hasColumn('batches', 'custom_mrl_remark')) {
                $table->text('custom_mrl_remark')->nullable();
            }
            if (! Schema::hasColumn('batches', 'custom_separation_remark')) {
                $table->text('custom_separation_remark')->nullable();
            }
            if (! Schema::hasColumn('batches', 'start_time')) {
                $table->timestamp('start_time')->nullable();
            }
            if (! Schema::hasColumn('batches', 'last_saved_at')) {
                $table->timestamp('last_saved_at')->nullable();
            }
            if (! Schema::hasColumn('batches', 'last_saved_by_user_id')) {
                $table->foreignId('last_saved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            }
        });

        // 3. Add shift handover & user tracking per sack row in weighing_items
        Schema::table('weighing_items', function (Blueprint $table) {
            if (! Schema::hasColumn('weighing_items', 'created_by_user_id')) {
                $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            }
            if (! Schema::hasColumn('weighing_items', 'shift')) {
                $table->string('shift')->nullable();
            }
            if (! Schema::hasColumn('weighing_items', 'group')) {
                $table->string('group')->nullable();
            }
        });

        // 4. Create batch_interim_separations table for pause/stop workflow
        if (! Schema::hasTable('batch_interim_separations')) {
            Schema::create('batch_interim_separations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('shift')->nullable();
                $table->string('group')->nullable();
                $table->decimal('separation_product_kg', 10, 2)->default(0);
                $table->decimal('separation_bits_stem_kg', 10, 2)->default(0);
                $table->decimal('separation_dust_kg', 10, 2)->default(0);
                $table->decimal('separation_waste_kg', 10, 2)->default(0);
                $table->integer('sacks_processed_count')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_interim_separations');

        Schema::table('weighing_items', function (Blueprint $table) {
            $table->dropForeign(['created_by_user_id']);
            $table->dropColumn(['created_by_user_id', 'shift', 'group']);
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['supervisor_approved_by_user_id', 'last_saved_by_user_id']);
            $table->dropColumn([
                'supervisor_approval_status',
                'supervisor_approved_at',
                'supervisor_approved_by_user_id',
                'custom_dn_remark',
                'custom_mrl_remark',
                'custom_separation_remark',
                'start_time',
                'last_saved_at',
                'last_saved_by_user_id',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['shift', 'group', 'customer_id']);
        });
    }
};
