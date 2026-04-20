<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracions', function (Blueprint $table) {
            $table->string('branding_logo_path', 2048)->nullable()->after('aceptar_pagos_con_tarjeta');
        });
    }

    public function down(): void
    {
        Schema::table('configuracions', function (Blueprint $table) {
            $table->dropColumn('branding_logo_path');
        });
    }
};
