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
        Schema::table('eventos', function (Blueprint $table) {

            // Coordenadas GPS
            $table->decimal('latitud', 10, 7)
                ->nullable()
                ->after('token_evento');

            $table->decimal('longitud', 10, 7)
                ->nullable()
                ->after('latitud');

            // Radio permitido en metros
            $table->integer('radio_permitido')
                ->nullable()
                ->after('longitud');

            // Nombre o dirección del lugar
            $table->string('direccion_texto', 255)
                ->nullable()
                ->after('radio_permitido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {

            $table->dropColumn([
                'latitud',
                'longitud',
                'radio_permitido',
                'direccion_texto'
            ]);
        });
    }
};
