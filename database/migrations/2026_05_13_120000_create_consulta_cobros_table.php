<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta_cobros', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cita_id')->unique()->constrained('citas')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('paciente_id')->constrained('users')->cascadeOnDelete();
            $table->string('estado_instrucciones', 32)->default('pendiente')->index();
            $table->text('instrucciones_cobro')->nullable();
            $table->time('hora_fin_original')->nullable();
            $table->time('hora_fin_proyectada')->nullable();
            $table->unsignedInteger('duracion_extra_minutos')->default(0);
            $table->decimal('subtotal_servicios', 10, 2)->default(0);
            $table->decimal('subtotal_articulos', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('estado_cobro', 32)->default('pendiente')->index();
            $table->foreignId('enviado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('enviado_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta_cobros');
    }
};
