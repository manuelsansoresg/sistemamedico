<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->unsignedBigInteger('origen_suscripcion_id')->nullable()->after('created_by');
            $table->string('origen_tipo', 20)->nullable()->after('origen_suscripcion_id');
            $table->foreign('origen_suscripcion_id')
                ->references('id')
                ->on('suscripciones')
                ->onDelete('set null');
        });

        Schema::table('consultorios', function (Blueprint $table) {
            $table->unsignedBigInteger('origen_suscripcion_id')->nullable()->after('created_by');
            $table->string('origen_tipo', 20)->nullable()->after('origen_suscripcion_id');
            $table->foreign('origen_suscripcion_id')
                ->references('id')
                ->on('suscripciones')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropForeign(['origen_suscripcion_id']);
            $table->dropColumn(['origen_suscripcion_id', 'origen_tipo']);
        });

        Schema::table('consultorios', function (Blueprint $table) {
            $table->dropForeign(['origen_suscripcion_id']);
            $table->dropColumn(['origen_suscripcion_id', 'origen_tipo']);
        });
    }
};

