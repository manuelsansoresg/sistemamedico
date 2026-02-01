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
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->onDelete('set null');
            $table->foreignId('doctor_id')->constrained('users');
            $table->foreignId('paciente_id')->constrained('users');
            $table->foreignId('plantilla_id')->constrained('plantillas');
            
            // Historical health data snapshot
            $table->decimal('peso', 5, 2)->nullable();
            $table->decimal('estatura', 5, 2)->nullable();
            $table->text('alergias')->nullable();
            $table->text('diagnostico')->nullable(); // Just in case, general field
            
            $table->timestamps();
        });

        Schema::create('consulta_valores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas')->onDelete('cascade');
            $table->foreignId('plantilla_campo_id')->constrained('plantilla_campos')->onDelete('cascade');
            $table->text('valor')->nullable();
            $table->timestamps();
        });

        Schema::create('estudios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_id')->constrained('consultas')->onDelete('cascade');
            $table->text('orden'); // The list of studies ordered
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudios');
        Schema::dropIfExists('consulta_valores');
        Schema::dropIfExists('consultas');
    }
};
