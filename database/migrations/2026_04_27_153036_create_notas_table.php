<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('criterio_id')
                ->constrained('criterios')
                ->cascadeOnDelete();
                
            $table->foreignId('estudiante_id')
                ->constrained('estudiantes')
                ->cascadeOnDelete();

            $table->decimal('nota', 5, 2);

            $table->text('observacion')->nullable();

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['criterio_id', 'estudiante_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notas');
    }
};
