<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `city` works exactly like `category`: a small admin-managed lookup that
 * feeds a dropdown on the Add/Edit Event form.
 *
 * Guarded with hasTable() because fresh installs already get this table from
 * installer/eventright.sql — on those the deploy's `migrate --force` just
 * records this migration as run.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('city')) {
            return;
        }

        Schema::create('city', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city');
    }
};
