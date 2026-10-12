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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo',1);
            $table->string('cnpj_cpf',14)->unique();
            $table->string('razao_social_nome',100);  
            $table->string('nome_fantasia_apelido',100);
            $table->string('inscricao_estadual',20)->nullable();
            $table->string('inscricao_municipal',20)->nullable();
            $table->string('cnes',7)->nullable();
            $table->string('cep',8);    
            $table->string('logradouro',100);
            $table->string('numero',10);
            $table->string('complemento',50)->nullable();
            $table->string('bairro',50);
            $table->string('cidade',50);
            $table->string('uf',2);
            $table->string('telefone',15)->nullable();  
            $table->string('celular',15)->nullable();
            $table->string('email',100)->nullable();
            $table->text('observacoes')->nullable();
            $table->string('responsavel',100)->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
