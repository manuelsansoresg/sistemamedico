<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('doctor_patient', function (Blueprint $table) {
            $table->unsignedBigInteger('suscripcion_id')->nullable()->after('patient_id');
            $table->foreign('suscripcion_id')
                ->references('id')->on('suscripciones')
                ->onDelete('set null');
            $table->index('suscripcion_id');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_patient', function (Blueprint $table) {
            $table->dropForeign(['suscripcion_id']);
            $table->dropIndex(['suscripcion_id']);
            $table->dropColumn('suscripcion_id');
        });
    }
};

