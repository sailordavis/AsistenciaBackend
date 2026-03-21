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
          Schema::table('grupos', function ($table) {
        $table->string('turno')->change();
    });

    // Eliminar si ya existe
    DB::statement("
        ALTER TABLE grupos 
        DROP CONSTRAINT IF EXISTS grupos_turno_check
    ");

    // Crear nuevamente
    DB::statement("
        ALTER TABLE grupos 
        ADD CONSTRAINT grupos_turno_check 
        CHECK (turno IN ('Matutino', 'Vespertino'))
    ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          DB::statement("ALTER TABLE grupos DROP CONSTRAINT grupos_turno_check");

    Schema::table('grupos', function (Blueprint $table) {
        $table->string('turno')->change();
    });
    }
};
