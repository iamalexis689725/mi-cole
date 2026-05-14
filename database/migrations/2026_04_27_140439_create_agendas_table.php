<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agendas', function (Blueprint $table) {

            $table->id();

            $table->foreignId('asignacion_docente_id')
                ->constrained('asignaciones_docente')
                ->cascadeOnDelete();

            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['tarea', 'examen', 'recurso']);
            $table->dateTime('fecha_entrega')->nullable();
            $table->string('archivo')->nullable();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agendas');
    }
};
