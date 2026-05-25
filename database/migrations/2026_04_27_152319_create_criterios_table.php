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

            $table->foreignId('asignacion_docente_id')
                ->constrained('asignaciones_docente')
                ->cascadeOnDelete();

            $table->string('nombre');

            $table->decimal('porcentaje', 5, 2);

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();
                
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('criterios');
    }
};
