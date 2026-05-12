<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('anecdotarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('profesor_id')->constrained('profesores')->cascadeOnDelete();
            $table->foreignId('asignacion_docente_id')->constrained('asignaciones_docente')->cascadeOnDelete();
            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();

            $table->enum('tipo', ['conducta', 'merito', 'observacion']);
            $table->string('titulo');
            $table->text('descripcion');
            $table->date('fecha');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('anecdotarios');
    }
};
