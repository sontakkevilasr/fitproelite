<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trial_sessions', function (Blueprint $table) {
            $table->foreignId('trainer_category_id')->nullable()->after('trainer_profile_id')->constrained()->nullOnDelete();
        });

        // Backfill from each session's parent trial, which historically had
        // exactly one category for the whole trial.
        DB::statement('
            UPDATE trial_sessions ts
            INNER JOIN trials t ON t.id = ts.trial_id
            SET ts.trainer_category_id = t.trainer_category_id
            WHERE ts.trainer_category_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('trial_sessions', function (Blueprint $table) {
            $table->dropForeign(['trainer_category_id']);
            $table->dropColumn('trainer_category_id');
        });
    }
};
