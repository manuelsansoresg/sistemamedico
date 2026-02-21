<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurso_reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurso_id')->constrained('recursos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('titulo')->nullable();
            $table->text('comentario')->nullable();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->string('estado')->default('activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurso_reservas');
    }
};
