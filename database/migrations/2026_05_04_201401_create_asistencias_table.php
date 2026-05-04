<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_docente_id')
                ->constrained('asignaciones_docente')
                ->cascadeOnDelete();

            $table->date('fecha');

            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();
            
            $table->unique([
                'asignacion_docente_id',
                'fecha',
                'tenant_id'
            ], 'uniq_asistencia_clase_fecha');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asistencias');
    }
};
