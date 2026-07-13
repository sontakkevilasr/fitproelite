<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counsellor_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('call_date');
            $table->text('notes')->nullable();
            $table->string('outcome')->nullable();
            $table->date('next_follow_up_at')->nullable();
            $table->timestamps();

            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_calls');
    }
};
