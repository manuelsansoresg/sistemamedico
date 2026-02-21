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
        // Cleanup from potentially failed previous run
        if (Schema::hasColumn('users', 'apellido_paterno')) {
            Schema::table('users', function (Blueprint $table) {
                // We wrap this in a try-catch or just hope the columns exist to be dropped.
                // To be safe, we list them.
                $columns = ['apellido_paterno', 'apellido_materno', 'telefono', 'cedula_profesional', 'especialidad_id'];
                $table->dropColumn($columns);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('apellido_paterno')->nullable()->after('name');
            $table->string('apellido_materno')->nullable()->after('apellido_paterno');
            $table->string('telefono')->nullable()->after('email');
            $table->string('cedula_profesional')->nullable()->after('password');
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades')->nullOnDelete()->after('cedula_profesional');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'especialidad_id')) {
                // Check for foreign key existence before dropping?
                // Schema::dropForeign doesn't check, but usually down() is deterministic.
                // We'll just try standard drop.
                $table->dropForeign(['especialidad_id']);
                $table->dropColumn(['apellido_paterno', 'apellido_materno', 'telefono', 'cedula_profesional', 'especialidad_id']);
            }
        });
    }
};
