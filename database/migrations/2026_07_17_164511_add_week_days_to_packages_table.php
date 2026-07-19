<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Training frequency per week (3 or 6 days) — caps how many
            // total sessions a package may contain: week_days * 4 (weeks).
            $table->unsignedTinyInteger('week_days')->default(3)->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('week_days');
        });
    }
};
