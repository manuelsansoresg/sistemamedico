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
        Schema::create('configuracions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('aceptar_transferencia_bancaria')->default(false);
            $table->string('banco')->nullable();
            $table->string('titular')->nullable();
            $table->string('cuenta')->nullable();
            $table->string('clabe')->nullable();
            $table->boolean('aceptar_pagos_con_tarjeta')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracions');
    }
};
