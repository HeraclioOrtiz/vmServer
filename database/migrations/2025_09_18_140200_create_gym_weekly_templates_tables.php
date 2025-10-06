<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plantillas semanales (mapa de 7 días a plantillas diarias)
        Schema::create('gym_weekly_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('goal')->nullable();
            $table->string('split')->nullable(); // PPL | UpperLower | FullBody
            $table->unsignedTinyInteger('days_per_week')->default(3);
            $table->json('tags')->nullable();
            $table->boolean('is_preset')->default(false);
            $table->timestamps();
        });

        Schema::create('gym_weekly_template_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_template_id')->constrained('gym_weekly_templates')->cascadeOnDelete();
            $table->unsignedTinyInteger('weekday'); // 1=Lunes ... 7=Domingo
            $table->foreignId('daily_template_id')->nullable()->constrained('gym_daily_templates')->nullOnDelete();
            $table->timestamps();
            $table->unique(['weekly_template_id','weekday'], 'uniq_gwtd_template_weekday');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_weekly_template_days');
        Schema::dropIfExists('gym_weekly_templates');
    }
};
