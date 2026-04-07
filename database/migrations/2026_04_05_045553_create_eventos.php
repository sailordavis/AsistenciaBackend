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
    Schema::create('eventos', function (Blueprint $table) {
             $table->id();

    // Campos principales
    $table->string('titulo');
    $table->date('fecha');
    $table->time('hora_inicio');
    $table->time('hora_fin');

    // Tipo de evento
    $table->enum('tipo', ['clase', 'general']);

    // Relaciones
    $table->foreignId('clase_id')
          ->nullable()
          ->constrained('clases')
          ->nullOnDelete();

    $table->foreignId('creador_id')
          ->constrained('usuarios') 
          ->cascadeOnDelete();

    // Token QR
    $table->string('token_evento')->unique();

   
    $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
