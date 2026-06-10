<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asignaciones_docente', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_period_id')->constrained()->cascadeOnDelete();

            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profesor_id')->constrained('profesores')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paralelo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('asignaciones_docente');
    }
};