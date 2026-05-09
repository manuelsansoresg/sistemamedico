<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'audit_security_logs',
            'audit_subscription_logs',
            'audit_user_logs',
            'audit_clinical_logs',
            'audit_medical_logs',
            'audit_settings_logs',
        ];

        foreach ($tables as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->string('section')->nullable();
                $table->nullableMorphs('model');
                $table->json('payload')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_settings_logs');
        Schema::dropIfExists('audit_medical_logs');
        Schema::dropIfExists('audit_clinical_logs');
        Schema::dropIfExists('audit_user_logs');
        Schema::dropIfExists('audit_subscription_logs');
        Schema::dropIfExists('audit_security_logs');
    }
};
