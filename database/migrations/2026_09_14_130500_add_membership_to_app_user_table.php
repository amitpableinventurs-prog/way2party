<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('app_user', 'membership_plan_id')) {
            return;
        }

        Schema::table('app_user', function (Blueprint $table) {
            $table->unsignedBigInteger('membership_plan_id')->nullable()->after('instagram_id');
            $table->timestamp('membership_expires_at')->nullable()->after('membership_plan_id');

            $table->foreign('membership_plan_id')->references('id')->on('membership_plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('app_user', 'membership_plan_id')) {
            Schema::table('app_user', function (Blueprint $table) {
                $table->dropForeign(['membership_plan_id']);
                $table->dropColumn(['membership_plan_id', 'membership_expires_at']);
            });
        }
    }
};
