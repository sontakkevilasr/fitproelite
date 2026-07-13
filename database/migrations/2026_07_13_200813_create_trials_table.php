<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counsellor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booked_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trainer_category_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->unsignedTinyInteger('total_sessions')->default(1);
            $table->string('status')->default('scheduled');
            $table->text('outcome_notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trials');
    }
};
