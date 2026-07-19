<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainer_profiles', function (Blueprint $table) {
            $table->dropForeign(['trainer_category_id']);
            $table->dropColumn('trainer_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('trainer_profiles', function (Blueprint $table) {
            $table->foreignId('trainer_category_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }
};
