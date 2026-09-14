<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Controls who can see an event in public listings: everyone, members-only,
 * previously-attended customers only, or hidden from customers entirely.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('events', 'visibility')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->enum('visibility', ['everyone', 'members_only', 'previously_attended', 'hidden'])
                ->default('everyone')->after('featured_order');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'visibility')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('visibility');
            });
        }
    }
};
