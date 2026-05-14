<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_cobro_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('consulta_cobro_id')->constrained('consulta_cobros')->cascadeOnDelete();
            $table->string('tipo', 24)->index();
            $table->foreignId('servicio_id')->nullable()->constrained('servicios')->nullOnDelete();
            $table->foreignId('articulo_cobro_id')->nullable()->constrained('articulos_cobro')->nullOnDelete();
            $table->string('nombre_snapshot');
            $table->unsignedInteger('cantidad')->default(1);
            $table->unsignedInteger('duracion_minutos_snapshot')->default(0);
            $table->decimal('precio_catalogo', 10, 2)->default(0);
            $table->decimal('precio_cobrado', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->boolean('precio_modificado')->default(false);
            $table->foreignId('modificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('motivo_ajuste')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_cobro_items');
    }
};
