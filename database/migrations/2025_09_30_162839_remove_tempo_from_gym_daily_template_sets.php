<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gym_daily_template_sets', function (Blueprint $table) {
            if (Schema::hasColumn('gym_daily_template_sets', 'tempo')) {
                $table->dropColumn('tempo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gym_daily_template_sets', function (Blueprint $table) {
            $table->string('tempo')->nullable();
        });
    }
};
