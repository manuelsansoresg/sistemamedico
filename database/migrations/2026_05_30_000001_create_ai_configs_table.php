<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('provider')->default('openai');
            $table->text('api_key')->nullable();
            $table->string('model_for_summary')->nullable();
            $table->string('model_for_assistant')->nullable();
            $table->string('model_for_notes')->nullable();
            $table->string('model_for_diagnosis')->nullable();
            $table->string('model_for_prescription')->nullable();
            $table->boolean('enabled')->default(true);
            $table->json('provider_options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_configs');
    }
};
