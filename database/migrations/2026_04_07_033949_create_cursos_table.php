<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');

            $table->foreignId('academic_period_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('nombre');
            $table->string('nivel');
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);

            $table->timestamps();

            $table->unique(['tenant_id', 'academic_period_id', 'nombre']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cursos');
    }
};
