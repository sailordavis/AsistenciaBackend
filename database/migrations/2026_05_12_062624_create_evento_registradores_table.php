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
        Schema::create('evento_registradores', function (Blueprint $table) {
        $table->foreignId('evento_id')
        ->constrained('eventos')
        ->onDelete('cascade');

         $table->foreignId('usuario_id')
        ->constrained('usuarios')
        ->onDelete('cascade');

    $table->timestamps();

    $table->primary(['evento_id', 'usuario_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento_registradores');
    }
};
