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
        Schema::create('shared_expediente_permission_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shared_expediente_permission_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_role', 20);
            $table->string('terms_key', 80);
            $table->string('terms_hash', 64);
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->unique(['shared_expediente_permission_id', 'user_id', 'actor_role'], 'shared_permission_acceptance_unique');
            $table->foreign('shared_expediente_permission_id', 'sep_accept_permission_fk')
                ->references('id')
                ->on('shared_expediente_permissions')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_expediente_permission_acceptances');
    }
};
