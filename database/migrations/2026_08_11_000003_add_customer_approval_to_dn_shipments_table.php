<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dn_shipments', function (Blueprint $table) {
            $table->timestamp('customer_approved_at')->nullable()->after('status');
            $table->foreignId('customer_approved_by_user_id')->nullable()->after('customer_approved_at')->constrained('users')->nullOnDelete();
            $table->text('customer_approval_note')->nullable()->after('customer_approved_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('dn_shipments', function (Blueprint $table) {
            $table->dropForeign(['customer_approved_by_user_id']);
            $table->dropColumn(['customer_approved_at', 'customer_approved_by_user_id', 'customer_approval_note']);
        });
    }
};
