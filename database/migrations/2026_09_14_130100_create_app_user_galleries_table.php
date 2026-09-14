<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extra photo gallery for a customer's profile, separate from the single
 * primary `image` column on app_user.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('app_user_galleries')) {
            return;
        }

        Schema::create('app_user_galleries', function (Blueprint $table) {
            $table->id();
            $table->integer('app_user_id');
            $table->string('image');
            $table->timestamps();

            $table->index('app_user_id');
            $table->foreign('app_user_id')->references('id')->on('app_user')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_user_galleries');
    }
};
