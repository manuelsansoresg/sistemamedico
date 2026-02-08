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
        Schema::create('ganancias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('suscripcion_id')->constrained('suscripciones')->onDelete('cascade');
            $table->foreignId('catalogo_id')->nullable()->constrained('catalogos')->onDelete('set null');
            $table->decimal('monto_total', 10, 2); // Precio del item/servicio
            $table->decimal('monto_ganancia_doctor', 10, 2); // Ganancia del doctor
            $table->decimal('porcentaje_aplicado', 5, 2); // Porcentaje usado
            $table->string('concepto');
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ganancias');
    }
};
