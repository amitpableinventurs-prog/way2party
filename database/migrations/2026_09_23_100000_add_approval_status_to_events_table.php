<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Events posted by organizers wait for admin approval before going live.
    // Existing events default to 'approved' so nothing already live disappears.
    public function up(): void
    {
        if (Schema::hasColumn('events', 'approval_status')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('approved')->after('status');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'approval_status')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('approval_status');
            });
        }
    }
};
