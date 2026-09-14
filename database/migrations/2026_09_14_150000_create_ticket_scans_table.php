<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per scan attempt at the door (successful or not), so the scanner
 * portal can show a recent-activity feed. order_child_id is nullable
 * because an invalid/unrecognized code never resolves to a real ticket.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ticket_scans')) {
            return;
        }

        Schema::create('ticket_scans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scanner_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('order_child_id')->nullable();
            $table->string('ticket_number')->nullable();
            $table->enum('result', ['success', 'already_used', 'cancelled', 'invalid', 'expired'])->default('invalid');
            $table->string('message')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'created_at']);
            $table->index('scanner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_scans');
    }
};
