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
            // Campos específicos de la API del club (DNI ya existe)
            $table->string('nombre')->nullable();
            $table->string('apellido')->nullable();
            $table->string('nacionalidad')->nullable();
            $table->date('nacimiento')->nullable();
            $table->string('domicilio')->nullable();
            $table->string('localidad')->nullable();
            $table->string('telefono')->nullable();
            $table->string('celular')->nullable();
            $table->string('categoria')->nullable();
            $table->string('socio_id')->nullable();   // "Id" / "socio_n"
            $table->string('barcode')->nullable();
            $table->string('estado_socio')->nullable();
            $table->string('avatar_path')->nullable(); // almacenamiento local
            $table->timestamp('api_updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nombre','apellido','nacionalidad','nacimiento','domicilio','localidad',
                'telefono','celular','categoria','socio_id','barcode','estado_socio','avatar_path','api_updated_at'
            ]);
        });
    }
};
