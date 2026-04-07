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
        Schema::create('alumnos_clases', function (Blueprint $table) {
             $table->foreignId('alumno_id')
              ->constrained('alumnos')
              ->cascadeOnDelete();

        $table->foreignId('clase_id')
              ->constrained('clases')
              ->cascadeOnDelete();
        // Clave primaria compuesta
        $table->primary(['alumno_id', 'clase_id']);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnos_clases');
    }
};
