<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional city image shown in the home page's "Cities" section.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('city', 'image')) {
            return;
        }

        Schema::table('city', function (Blueprint $table) {
            $table->string('image')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('city', 'image')) {
            Schema::table('city', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
