<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('muscle_group')->nullable();
            $table->string('movement_pattern')->nullable();
            $table->string('equipment')->nullable();
            $table->string('difficulty')->nullable();
            $table->json('tags')->nullable();
            $table->text('instructions')->nullable();
            $table->string('tempo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_exercises');
    }
};
