<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('app_user', 'facebook_id')) {
            return;
        }

        Schema::table('app_user', function (Blueprint $table) {
            $table->string('facebook_id')->nullable()->after('bio');
            $table->string('instagram_id')->nullable()->after('facebook_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_user', 'facebook_id')) {
            Schema::table('app_user', function (Blueprint $table) {
                $table->dropColumn(['facebook_id', 'instagram_id']);
            });
        }
    }
};
