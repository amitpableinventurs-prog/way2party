<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'is_profile_private')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_profile_private')->default(0)->after('bio');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'is_profile_private')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_profile_private');
            });
        }
    }
};
