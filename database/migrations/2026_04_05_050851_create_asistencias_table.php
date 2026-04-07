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
        Schema::create('asistencias', function (Blueprint $table) {
           $table->id();

    // Relaciones
    $table->foreignId('evento_id')
          ->constrained('eventos')
          ->cascadeOnDelete();

    $table->foreignId('alumno_id')
          ->constrained('alumnos')
          ->cascadeOnDelete();

    // Estado de asistencia
    $table->enum('estado', ['presente', 'ausente', 'retardo']);

    // Hora de registro (cuando escanea o se registra)
    $table->timestamp('hora_registro')->nullable();

    $table->timestamps();

    // Evita duplicados: un alumno solo puede tener una asistencia por evento
    $table->unique(['evento_id', 'alumno_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
