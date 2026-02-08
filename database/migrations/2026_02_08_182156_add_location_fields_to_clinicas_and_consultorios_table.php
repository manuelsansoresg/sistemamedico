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
        Schema::table('clinicas', function (Blueprint $table) {
            $table->decimal('lat', 10, 8)->nullable()->after('direccion');
            $table->decimal('lng', 11, 8)->nullable()->after('lat');
        });

        Schema::table('consultorios', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('nombre');
            $table->decimal('lat', 10, 8)->nullable()->after('direccion');
            $table->decimal('lng', 11, 8)->nullable()->after('lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinicas', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng']);
        });

        Schema::table('consultorios', function (Blueprint $table) {
            $table->dropColumn(['direccion', 'lat', 'lng']);
        });
    }
};
