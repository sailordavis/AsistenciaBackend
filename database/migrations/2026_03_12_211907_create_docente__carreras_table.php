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
        Schema::create('docente__carreras', function (Blueprint $table) {
             $table->foreignId('docente_id')
              ->constrained('docentes')
              ->cascadeOnDelete();

            $table->foreignId('carrera_id')
              ->constrained('carreras')
              ->cascadeOnDelete();

            $table->primary(['docente_id', 'carrera_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docente__carreras');
    }
};
