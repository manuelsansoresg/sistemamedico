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
        Schema::create('dias_sin_citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Creator
            $table->string('motivo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->boolean('todo_el_dia')->default(false);
            $table->timestamps();
        });

        Schema::create('consultorio_dia_sin_cita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultorio_id')->constrained()->onDelete('cascade');
            $table->foreignId('dia_sin_cita_id')->constrained('dias_sin_citas')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultorio_dia_sin_cita');
        Schema::dropIfExists('dias_sin_citas');
    }
};
