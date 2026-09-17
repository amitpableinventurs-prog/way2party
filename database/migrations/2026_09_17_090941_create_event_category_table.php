<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Plain integer columns, no DB-level FK — matches events.city_id's
        // convention in this app (ids are int(11), integrity kept at the app layer).
        Schema::create('event_category', function (Blueprint $table) {
            $table->id();
            $table->integer('event_id');
            $table->integer('category_id');
            $table->timestamps();

            $table->unique(['event_id', 'category_id']);
            $table->index('category_id');
        });

        // Backfill: every existing event's single category becomes its first pivot row,
        // so nothing that reads the new relation sees an empty list for old data.
        DB::table('events')
            ->whereNotNull('category_id')
            ->select('id', 'category_id')
            ->orderBy('id')
            ->chunk(200, function ($events) {
                $rows = $events->map(fn ($event) => [
                    'event_id' => $event->id,
                    'category_id' => $event->category_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all();
                DB::table('event_category')->insertOrIgnore($rows);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_category');
    }
};
