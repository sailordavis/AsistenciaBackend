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
        Schema::create('grupo__alumnos', function (Blueprint $table) {
            $table->foreignId('grupo_id')
              ->constrained('grupos')
              ->cascadeOnDelete();

            $table->foreignId('alumno_id')
              ->constrained('alumnos')
              ->cascadeOnDelete();

            $table->primary(['grupo_id', 'alumno_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupo__alumnos');
    }
};
