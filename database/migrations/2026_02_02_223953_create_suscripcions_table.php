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
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('paquete_id')->constrained('paquetes')->onDelete('restrict');
            $table->decimal('precio', 10, 2);
            $table->enum('metodo_pago', ['tarjeta', 'transferencia']);
            $table->enum('estatus_pago', ['pendiente', 'pagado', 'rechazado', 'cancelado'])->default('pendiente');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->string('comprobante_pago')->nullable(); // Ruta del archivo
            $table->string('referencia_pago')->nullable(); // ID de transacción o referencia bancaria
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
    }
};
