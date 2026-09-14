<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Whether an organizer allows customers to cancel their ticket for this
 * event, and the charge (if any) deducted from the refund.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('events', 'cancellation_allowed')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('cancellation_allowed')->default(false)->after('visibility');
            $table->decimal('cancellation_charges', 10, 2)->default(0)->after('cancellation_allowed');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'cancellation_allowed')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn(['cancellation_allowed', 'cancellation_charges']);
            });
        }
    }
};
