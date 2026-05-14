<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articulos_cobro', function (Blueprint $table): void {
            $table->unique(['doctor_id', 'nombre'], 'articulos_cobro_doctor_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('articulos_cobro', function (Blueprint $table): void {
            $table->dropUnique('articulos_cobro_doctor_nombre_unique');
        });
    }
};
