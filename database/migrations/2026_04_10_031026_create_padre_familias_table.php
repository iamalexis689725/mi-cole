<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::create('padre_familias', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('telefono')->nullable();
            $table->string('ocupacion')->nullable();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('padre_familias');
    }
};
