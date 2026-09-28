<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Admin-managed tag list (like `city`) that feeds the Tags multi-select on the
 * Add/Edit Event form. Events keep storing tag names in the comma-separated
 * `events.tags` column, so the API and older code paths are unaffected.
 *
 * Seeds the table from tags already typed on existing events so their
 * /tag/... links keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->integer('status')->default(1);
                $table->timestamps();
            });
        }

        $existing = DB::table('tags')->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
        $now = now();
        DB::table('events')->whereNotNull('tags')->where('tags', '!=', '')->pluck('tags')
            ->flatMap(fn ($tags) => explode(',', $tags))
            ->map(fn ($name) => trim($name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->reject(fn ($name) => in_array(mb_strtolower($name), $existing))
            ->each(fn ($name) => DB::table('tags')->insert([
                'name' => $name, 'status' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]));

        foreach (['tag_access', 'tag_create', 'tag_edit', 'tag_delete'] as $name) {
            Permission::findOrCreate($name, 'web');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');

        Permission::whereIn('name', ['tag_access', 'tag_create', 'tag_edit', 'tag_delete'])
            ->where('guard_name', 'web')
            ->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
