<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_professor')) {
                $table->boolean('is_professor')->default(false)->after('user_type');
                $table->index('is_professor', 'idx_users_is_professor');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_professor')) {
                $table->dropIndex('idx_users_is_professor');
                $table->dropColumn('is_professor');
            }
        });
    }
};
