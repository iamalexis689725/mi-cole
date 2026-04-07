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
            $table->timestamps();
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('asignaciones_docente');
    }
};
