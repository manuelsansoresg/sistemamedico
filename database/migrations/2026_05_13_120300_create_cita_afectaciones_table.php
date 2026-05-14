<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cita_afectaciones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('consulta_cobro_id')->constrained('consulta_cobros')->cascadeOnDelete();
            $table->foreignId('cita_origen_id')->constrained('citas')->cascadeOnDelete();
            $table->foreignId('cita_afectada_id')->constrained('citas')->cascadeOnDelete();
            $table->foreignId('paciente_afectado_id')->constrained('users')->cascadeOnDelete();
            $table->string('paciente_nombre_snapshot');
            $table->string('paciente_telefono_snapshot')->nullable();
            $table->string('paciente_email_snapshot')->nullable();
            $table->time('hora_inicio_original');
            $table->time('hora_fin_original')->nullable();
            $table->string('estado_original', 32)->nullable();
            $table->time('hora_fin_origen_proyectada');
            $table->string('estado', 32)->default('pendiente_aviso')->index();
            $table->text('notas')->nullable();
            $table->timestamp('avisado_at')->nullable();
            $table->foreignId('gestionado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reagendada_cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->timestamps();

            $table->unique(['consulta_cobro_id', 'cita_afectada_id'], 'cobro_cita_afectada_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_afectaciones');
    }
};
