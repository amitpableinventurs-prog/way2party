<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nullable so existing events (created before cities existed) stay valid.
 * New events require a city at the validation layer (EventController::store).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('events', 'city_id')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->integer('city_id')->nullable()->after('category_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'city_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('city_id');
            });
        }
    }
};
