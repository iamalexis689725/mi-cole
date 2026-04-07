<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('paralelos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('curso_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');

            $table->string('nombre');
            $table->string('turno')->nullable();
            $table->integer('capacidad')->nullable();
            $table->timestamps();

            $table->unique(['curso_id', 'nombre', 'tenant_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('paralelos');
    }
};
