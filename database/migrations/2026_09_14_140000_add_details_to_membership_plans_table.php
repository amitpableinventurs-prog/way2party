<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `amount` is the plan's face/quoted value shown to customers, separate
 * from `price` (the actual selling price) so admins can show a discount.
 * `perks` (free text) is replaced by `description` + the structured
 * membership_features list.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('membership_plans', 'amount')) {
            return;
        }

        Schema::table('membership_plans', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->default(0)->after('name');
            $table->string('image')->nullable()->after('price');
            $table->text('description')->nullable()->after('image');
        });

        if (Schema::hasColumn('membership_plans', 'perks')) {
            DB::statement('UPDATE membership_plans SET description = perks WHERE perks IS NOT NULL');
            Schema::table('membership_plans', function (Blueprint $table) {
                $table->dropColumn('perks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('membership_plans', 'amount')) {
            Schema::table('membership_plans', function (Blueprint $table) {
                $table->dropColumn(['amount', 'image', 'description']);
            });
        }
    }
};
