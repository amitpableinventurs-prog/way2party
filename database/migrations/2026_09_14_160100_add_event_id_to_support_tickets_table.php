<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional link from a support ticket to the event it's about, so the
 * organizer of that event can be notified of activity on it (read-only -
 * the ticket is still owned/answered by site admins, not the organizer).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('support_tickets', 'event_id')) {
            return;
        }

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->integer('event_id')->nullable()->after('app_user_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('support_tickets', 'event_id')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropColumn('event_id');
            });
        }
    }
};
