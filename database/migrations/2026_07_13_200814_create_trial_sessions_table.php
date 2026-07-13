<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trial_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trainer_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('session_number');
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('scheduled');
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['trial_id', 'session_number']);
            $table->unique(['trainer_profile_id', 'session_date', 'start_time'], 'trial_sessions_no_double_booking');
            $table->index(['trainer_profile_id', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_sessions');
    }
};
