<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A message thread between one customer (AppUser) and one organizer (User).
 * Optionally tied to the event the conversation started from.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('conversations')) {
            return;
        }

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_user_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['app_user_id', 'user_id', 'event_id']);
            $table->index('user_id');
            $table->index('app_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
