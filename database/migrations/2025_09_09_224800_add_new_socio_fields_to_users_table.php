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
            // Nuevos campos basados en la estructura actualizada de la API
            $table->string('socio_n')->nullable(); // Número de socio adicional
            $table->decimal('saldo', 10, 2)->default(0.00); // Saldo cuenta corriente
            $table->integer('semaforo')->default(1); // Estado de deuda: 1=al día, 99=deuda exigible, 10=deuda no exigible
            $table->string('tipo_dni')->nullable(); // Tipo de DNI
            $table->string('r1')->nullable(); // Campo r1 de la API
            $table->string('r2')->nullable(); // Campo r2 de la API
            $table->string('tutor')->nullable(); // Campo tutor
            $table->text('observaciones')->nullable(); // Observaciones del socio
            $table->decimal('deuda', 10, 2)->default(0.00); // Deuda
            $table->decimal('descuento', 10, 2)->default(0.00); // Descuento
            $table->date('alta')->nullable(); // Fecha de alta
            $table->boolean('suspendido')->default(false); // Estado suspendido
            $table->boolean('facturado')->default(true); // Estado facturado
            $table->date('fecha_baja')->nullable(); // Fecha de baja
            $table->decimal('monto_descuento', 10, 2)->nullable(); // Monto descuento
            $table->timestamp('update_ts')->nullable(); // Timestamp de actualización de la API
            $table->boolean('validmail_st')->default(false); // Estado validación email
            $table->timestamp('validmail_ts')->nullable(); // Timestamp validación email
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'socio_n', 'saldo', 'semaforo', 'tipo_dni', 'r1', 'r2', 'tutor', 
                'observaciones', 'deuda', 'descuento', 'alta', 'suspendido', 
                'facturado', 'fecha_baja', 'monto_descuento', 'update_ts', 
                'validmail_st', 'validmail_ts'
            ]);
        });
    }
};
