<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->unsignedSmallInteger('sessions_count')->nullable()->after('price');

            // Not wired into the actual booking flow yet — TrialBookingService
            // still always books exactly 3 free-trial sessions regardless of
            // this value. Captured now so it's on record per package; making
            // it actually drive the session count is a later step.
            $table->unsignedTinyInteger('trial_sessions_count')->default(3)->after('sessions_count');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['sessions_count', 'trial_sessions_count']);
        });
    }
};
