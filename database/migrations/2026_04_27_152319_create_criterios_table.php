<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('criterios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_docente_id')->constrained('asignacion_docentes');
            $table->string('nombre');
            $table->integer('porcentaje');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('criterios');
    }
};
