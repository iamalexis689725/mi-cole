<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('periodos_evaluacion', function (Blueprint $table) {
            $table->id();

            $table->foreignId('academic_period_id')
                ->constrained('academic_periods')
                ->cascadeOnDelete();

            $table->string('nombre');
            $table->integer('orden');

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('periodos_evaluacion');
    }
};
