<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records when a customer cancelled a booking and what charge (if any)
 * was deducted from their refund.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'cancelled_at')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('org_pay_status');
            $table->decimal('cancellation_charge_applied', 10, 2)->default(0)->after('cancelled_at');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'cancelled_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn(['cancelled_at', 'cancellation_charge_applied']);
            });
        }
    }
};
