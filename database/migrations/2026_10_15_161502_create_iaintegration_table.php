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
        Schema::create('iaintegration', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_ans')->nullable();
            $table->string('guia_prestador');
            $table->string('guia_operadora')->nullable();
            $table->string('carteira')->nullable();            
            $table->string('nome_beneficiario')->nullable();
            $table->string('validade_carteira')->nullable();
            $table->string('codigo_operadora')->nullable();
            $table->string('nome_contratado')->nullable();
            $table->string('cnes')->nullable();
            $table->string('profissional_executante')->nullable();
            $table->string('conselho_profissional')->nullable();
            $table->string('numero_conselho')->nullable();
            $table->string('atendimento_rn',1)->nullable();
            $table->string('ia_codigo')->nullable();
            $table->string('ia_descricao')->nullable();
            $table->string('data_atendimento')->nullable();
            $table->string('tp_cons_codigo')->nullable();
            $table->string('tp_cons_descricao')->nullable();
            $table->string('cobertura_especial')->nullable();
            $table->string('regime_atendimento')->nullable();
            $table->string('saude_ocupacional')->nullable();
            $table->integer('integrada')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iaintegration');
    }
};
