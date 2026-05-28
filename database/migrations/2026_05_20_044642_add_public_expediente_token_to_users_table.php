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
            $table->string('patient_public_token', 96)->nullable()->unique()->after('perfil_compartido');
            $table->timestamp('patient_public_token_regenerated_at')->nullable()->after('patient_public_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['patient_public_token']);
            $table->dropColumn(['patient_public_token', 'patient_public_token_regenerated_at']);
        });
    }
};
