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
            $table->enum('estatus_cedula', ['na', 'pendiente', 'validada', 'rechazada'])->default('na')->after('cedula_profesional');
            $table->timestamp('cedula_validada_at')->nullable()->after('estatus_cedula');
        });

        Schema::table('suscripciones', function (Blueprint $table) {
            $table->string('token_pago')->nullable()->after('referencia_pago'); // Token para subir comprobante
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['estatus_cedula', 'cedula_validada_at']);
        });

        Schema::table('suscripciones', function (Blueprint $table) {
            $table->dropColumn('token_pago');
        });
    }
};
