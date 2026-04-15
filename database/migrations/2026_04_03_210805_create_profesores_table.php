<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('profesores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('codigo_profesor');
            $table->unique(['codigo_profesor', 'tenant_id']);
            $table->string('especialidad')->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('profesores');
    }
};
