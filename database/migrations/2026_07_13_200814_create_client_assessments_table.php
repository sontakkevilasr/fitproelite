<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trial_id')->constrained()->cascadeOnDelete();
            $table->boolean('first_time_gym')->default(false);
            $table->string('workout_objective');
            $table->text('medical_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recommended_category_id')->nullable()->constrained('trainer_categories')->nullOnDelete();
            $table->foreignId('filled_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_assessments');
    }
};
