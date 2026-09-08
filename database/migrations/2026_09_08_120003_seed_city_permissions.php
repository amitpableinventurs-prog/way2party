<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Registers the city_* permissions so they show up in the Roles editor,
 * mirroring category_access / category_create / category_edit / category_delete.
 * Admins bypass gates via AuthServiceProvider, so this only matters for other
 * roles that should manage cities.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['city_access', 'city_create', 'city_edit', 'city_delete'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', ['city_access', 'city_create', 'city_edit', 'city_delete'])
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
