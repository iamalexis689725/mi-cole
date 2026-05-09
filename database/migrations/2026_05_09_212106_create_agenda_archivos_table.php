<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agenda_archivos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('agenda_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('archivo');

            $table->string('nombre_original');

            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agenda_archivos');
    }
};
