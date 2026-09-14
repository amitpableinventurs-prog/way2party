<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog of tickable membership benefits (e.g. "Message organizers",
 * "Access secret parties"), admin-extensible. Which features a given plan
 * includes is stored in membership_plan_features.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('membership_features')) {
            return;
        }

        Schema::create('membership_features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('membership_plan_features', function (Blueprint $table) {
            $table->unsignedBigInteger('membership_plan_id');
            $table->unsignedBigInteger('membership_feature_id');

            $table->primary(['membership_plan_id', 'membership_feature_id'], 'plan_feature_primary');
            $table->foreign('membership_plan_id')->references('id')->on('membership_plans')->cascadeOnDelete();
            $table->foreign('membership_feature_id')->references('id')->on('membership_features')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_plan_features');
        Schema::dropIfExists('membership_features');
    }
};
