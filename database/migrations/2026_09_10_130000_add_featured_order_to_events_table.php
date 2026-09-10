<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Priority for featured events. Higher number = shown higher up in the
 * "Featured Events" section on the home page. 0 = no explicit priority.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('events', 'featured_order')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->integer('featured_order')->default(0)->after('is_featured');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'featured_order')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('featured_order');
            });
        }
    }
};
