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
        Schema::create('plantillas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // The doctor who owns/uses this template
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // Who created it
            $table->timestamps();
        });

        Schema::create('plantilla_campos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_id')->constrained('plantillas')->onDelete('cascade');
            $table->string('nombre'); // Label
            $table->string('slug');
            $table->enum('tipo', ['text', 'date', 'textarea', 'select']);
            $table->boolean('es_obligatorio')->default(false);
            $table->json('opciones')->nullable(); // For select options
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantilla_campos');
        Schema::dropIfExists('plantillas');
    }
};
