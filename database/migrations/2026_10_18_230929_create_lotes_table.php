<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credenciado_id')->constrained('credenciados');
            $table->foreignId('convenio_id')->constrained('convenios');
            $table->string('numero');
            $table->string('protocolo')->nullable();
            $table->string('tipo',1);
            $table->date('data');
            $table->date('data_entrega');            
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('pago', 10, 2)->nullable();
            $table->date('data_pagamento')->nullable();
            $table->string('status')->default('pendente');            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
