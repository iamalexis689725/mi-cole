<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();

            $table->string("nombre");
            $table->date("fecha_inicio");
            $table->date("fecha_fin");

            $table->boolean("activo")->default(true);

            $table->foreignId("tenant_id")->constrained()->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('academic_periods');
    }
};
