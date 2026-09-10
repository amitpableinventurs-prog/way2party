<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Featured events are highlighted in a dedicated section on the frontend home page.
 * Admins and Organizers can toggle it from the event create/edit form or the event list.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('events', 'is_featured')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_featured')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'is_featured')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('is_featured');
            });
        }
    }
};
