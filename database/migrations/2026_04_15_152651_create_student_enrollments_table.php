<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('estudiante_id')->constrained()->onDelete('cascade');
            $table->foreignId('curso_id')->constrained()->onDelete('cascade');
            $table->foreignId('paralelo_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_period_id')->constrained()->onDelete('cascade');

            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');

            $table->timestamps();

            $table->unique([
                'estudiante_id',
                'academic_period_id',
                'tenant_id'
            ],"inscripcion_unica");
        });
    }


    public function down()
    {
        Schema::dropIfExists('inscripciones');
    }
};
