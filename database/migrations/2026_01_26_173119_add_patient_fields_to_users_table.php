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
            $table->string('curp', 18)->nullable()->after('email');
            $table->date('fecha_nacimiento')->nullable()->after('curp');
            $table->enum('sexo', ['M', 'F'])->nullable()->after('fecha_nacimiento'); // M = Masculino, F = Femenino
            $table->string('direccion')->nullable()->after('sexo');
            $table->string('numero_imss', 20)->nullable()->after('direccion');
            $table->boolean('activo')->default(true)->after('numero_imss');
            $table->boolean('perfil_compartido')->default(false)->after('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'curp',
                'fecha_nacimiento',
                'sexo',
                'direccion',
                'numero_imss',
                'activo',
                'perfil_compartido',
            ]);
        });
    }
};
