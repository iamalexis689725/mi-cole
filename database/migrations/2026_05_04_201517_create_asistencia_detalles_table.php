<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('asistencia_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asistencia_id')
                ->constrained('asistencias')
                ->cascadeOnDelete();

            $table->foreignId('estudiante_id')
                ->constrained('estudiantes')
                ->cascadeOnDelete();

            $table->enum('estado', [
                'presente',
                'falta',
                'justificado',
                'tarde'
            ])->default('presente');

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->unique([
                'asistencia_id',
                'estudiante_id'
            ], 'uniq_asistencia_estudiante');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asistencia_detalles');
    }
};
