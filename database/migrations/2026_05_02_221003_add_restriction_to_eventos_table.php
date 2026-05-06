<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE eventos
            ADD CONSTRAINT eventos_tipo_clase_check
            CHECK (
                (tipo = 'clase' AND clase_id IS NOT NULL)
                OR
                (tipo = 'general' AND clase_id IS NULL)
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE eventos
            DROP CONSTRAINT eventos_tipo_clase_check
        ");
    }
};
