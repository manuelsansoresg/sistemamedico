<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ganancias', function (Blueprint $table) {
            $table->string('tipo_ingreso', 20)->default('compra')->after('concepto');
        });

        DB::table('ganancias')->update(['tipo_ingreso' => 'compra']);
    }

    public function down(): void
    {
        Schema::table('ganancias', function (Blueprint $table) {
            $table->dropColumn('tipo_ingreso');
        });
    }
};
