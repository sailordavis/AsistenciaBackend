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
        $table->dropForeign([
        'clase_id'
    ]);

    $table->foreign('clase_id')
        ->references('id')
        ->on('clases')
        ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
             $table->dropForeign([
        'clase_id'
        ]);

        $table->foreign('clase_id')
        ->references('id')
        ->on('clases')
        ->onDelete('set null');
        });
    }
};
