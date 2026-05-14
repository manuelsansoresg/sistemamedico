<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table): void {
            $table->unique(['created_by', 'nombre'], 'servicios_created_by_nombre_unique');
        });
    }

    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table): void {
            $table->dropUnique('servicios_created_by_nombre_unique');
        });
    }
};
