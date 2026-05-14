<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ganancias', function (Blueprint $table): void {
            $table->foreignId('consulta_cobro_id')->nullable()->after('suscripcion_id')->constrained('consulta_cobros')->nullOnDelete();
        });

        Schema::table('ganancias', function (Blueprint $table): void {
            $table->dropForeign(['suscripcion_id']);
            $table->foreignId('suscripcion_id')->nullable()->change();
            $table->foreign('suscripcion_id')->references('id')->on('suscripciones')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ganancias', function (Blueprint $table): void {
            $table->dropForeign(['consulta_cobro_id']);
            $table->dropColumn('consulta_cobro_id');
            $table->dropForeign(['suscripcion_id']);
            $table->foreignId('suscripcion_id')->nullable(false)->change();
            $table->foreign('suscripcion_id')->references('id')->on('suscripciones')->cascadeOnDelete();
        });
    }
};
