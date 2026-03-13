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
        $table->string('titulo');
        $table->text('descripcion')->nullable();

        $table->string('ubicacion');

        $table->date('fecha');
        $table->time('hora_inicio');
        $table->time('hora_fin');

        $table->integer('cupo')->nullable();

        $table->string('imagen')->nullable();

        $table->foreignId('creador_id')
              ->constrained('usuarios')
              ->cascadeOnDelete();

        $table->boolean('activo')->default(true);
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
