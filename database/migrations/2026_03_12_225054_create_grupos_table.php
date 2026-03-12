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
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('unidad_academica_id')
              ->constrained('unidad_academicas')
              ->cascadeOnDelete();

        $table->foreignId('docente_id')
              ->constrained('docentes')
              ->cascadeOnDelete();

        $table->foreignId('carrera_id')
              ->nullable()
              ->constrained('carreras')
              ->nullOnDelete();

        $table->foreignId('periodo_id')
              ->constrained('periodo_academicos')
              ->cascadeOnDelete();

        $table->integer('semestre')->nullable();

        $table->enum('turno', ['mañana', 'tarde']);

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
