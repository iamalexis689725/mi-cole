<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('criterios', function (Blueprint $table) {
            $table->foreignId('periodo_evaluacion_id')
                ->nullable()
                ->after('asignacion_docente_id')
                ->constrained('periodos_evaluacion')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('criterios', function (Blueprint $table) {
            $table->dropForeign(['periodo_evaluacion_id']);
            $table->dropColumn('periodo_evaluacion_id');
        });
    }
};
