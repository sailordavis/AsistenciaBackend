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
        Schema::create('evento__grupos', function (Blueprint $table) {
            $table->foreignId('evento_id')
              ->constrained('eventos')
              ->cascadeOnDelete();

            $table->foreignId('grupo_id')
              ->constrained('grupos')
              ->cascadeOnDelete();

            $table->primary(['evento_id', 'grupo_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento__grupos');
    }
};
