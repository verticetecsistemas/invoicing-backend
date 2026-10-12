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
        Schema::create('guias_consulta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_id')->constrained('guias');
            $table->foreignId('executante_id')->constrained('medicos');
            $table->foreignId('auxiliar_id')->constrained('medicos');
            $table->string('tipo',1);
            $table->string('tabela',2);
            $table->string('codigo',10);
            $table->string('descricao',50);
            $table->integer('quantidade');
            $table->decimal('valor', 10, 2);
            $table->decimal('valor_total', 10, 2);
            $table->decimal('valor_pago', 10, 2);
            $table->date('data_pagamento')->nullable();
            $table->string('atendimento_rn',1);
            $table->string('indicacao_acidente',1);
            $table->string('cobertura_especial',2)->nullable();
            $table->string('regime_atendimento',2);
            $table->string('ocupacional',2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guias_consulta');
    }
};
