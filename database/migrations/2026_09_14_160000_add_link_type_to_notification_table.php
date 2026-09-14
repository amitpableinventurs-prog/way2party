<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The existing notification system only ever pointed at an order (built
 * for "new booking" alerts). `type` + `link` let it also carry
 * message/support-ticket notifications, which have nothing to do with an
 * order_id, without breaking the order-based accessors already in
 * App\Models\Notification.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('notification', 'link')) {
            return;
        }

        Schema::table('notification', function (Blueprint $table) {
            $table->string('type')->default('order')->after('order_id');
            $table->string('link')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('notification', 'link')) {
            Schema::table('notification', function (Blueprint $table) {
                $table->dropColumn(['type', 'link']);
            });
        }
    }
};
