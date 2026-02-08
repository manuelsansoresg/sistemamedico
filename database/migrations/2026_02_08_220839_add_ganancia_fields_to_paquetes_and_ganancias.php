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
        Schema::table('paquetes', function (Blueprint $table) {
            $table->decimal('porcentaje_ganancia', 5, 2)->default(0)->after('precio');
        });

        Schema::table('ganancias', function (Blueprint $table) {
            $table->foreignId('paquete_id')->nullable()->after('catalogo_id')->constrained('paquetes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ganancias', function (Blueprint $table) {
            $table->dropForeign(['paquete_id']);
            $table->dropColumn('paquete_id');
        });

        Schema::table('paquetes', function (Blueprint $table) {
            $table->dropColumn('porcentaje_ganancia');
        });
    }
};
