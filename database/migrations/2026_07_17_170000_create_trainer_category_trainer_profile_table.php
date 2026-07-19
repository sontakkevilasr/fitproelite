<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainer_category_trainer_profile', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_category_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['trainer_profile_id', 'trainer_category_id'], 'trainer_category_trainer_profile_unique');
        });

        // Backfill from the existing single trainer_category_id column before it's dropped.
        DB::table('trainer_profiles')
            ->whereNotNull('trainer_category_id')
            ->select('id', 'trainer_category_id')
            ->orderBy('id')
            ->each(function ($profile) {
                DB::table('trainer_category_trainer_profile')->insert([
                    'trainer_profile_id' => $profile->id,
                    'trainer_category_id' => $profile->trainer_category_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_category_trainer_profile');
    }
};
