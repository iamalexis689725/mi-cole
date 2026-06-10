<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('horarios_asignacion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asignacion_docente_id')
                ->constrained('asignaciones_docente')
                ->cascadeOnDelete();

            $table->string('dia');
            $table->time('hora_inicio');
            $table->time('hora_fin');

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('horarios_asignacion');
    }
};
