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
        Schema::create('clases', function (Blueprint $table) {
        $table->id();
        
        $table->string('nombre'); // varchar
        $table->unsignedBigInteger('docente_id');
        $table->text('descripcion')->nullable();

        $table->timestamps();

        // Foreign key
        $table->foreign('docente_id')
              ->references('id')
              ->on('docentes')
              ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clases');
    }
};
