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
        Schema::table('users', function (Blueprint $table) {
            // Agregar campo student_gym después de is_professor
            if (!Schema::hasColumn('users', 'student_gym')) {
                $table->boolean('student_gym')->default(false)->after('is_professor');
                $table->timestamp('student_gym_since')->nullable()->after('student_gym');
                $table->index('student_gym', 'idx_users_student_gym');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'student_gym')) {
                $table->dropIndex('idx_users_student_gym');
                $table->dropColumn(['student_gym', 'student_gym_since']);
            }
        });
    }
};
