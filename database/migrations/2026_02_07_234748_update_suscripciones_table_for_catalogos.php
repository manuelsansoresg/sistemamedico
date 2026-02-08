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
        Schema::table('suscripciones', function (Blueprint $table) {
            $table->string('tipo')->default('paquete')->after('id'); // paquete, individual
            $table->unsignedBigInteger('paquete_id')->nullable()->change();
            $table->foreignId('catalogo_id')->nullable()->after('paquete_id')->constrained('catalogos')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suscripciones', function (Blueprint $table) {
            $table->dropForeign(['catalogo_id']);
            $table->dropColumn('catalogo_id');
            $table->unsignedBigInteger('paquete_id')->nullable(false)->change();
            $table->dropColumn('tipo');
        });
    }
};
