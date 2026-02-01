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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('peso', 5, 2)->nullable()->after('numero_imss'); // kg
            $table->decimal('estatura', 5, 2)->nullable()->after('peso'); // cm or m? Usually cm (170) or m (1.70). Let's use m (1.70) so 5,2 covers 999.99
            $table->text('alergias')->nullable()->after('estatura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['peso', 'estatura', 'alergias']);
        });
    }
};
