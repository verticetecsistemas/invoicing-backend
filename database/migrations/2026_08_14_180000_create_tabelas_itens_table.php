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
        Schema::create('tabelas_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tabelas_id')->constrained('tabelas');
            $table->string('codigo', 10);
            $table->string('descricao', 65);
            $table->decimal('operacional', 14, 4)->default(0);
            $table->decimal('honorario', 14, 4)->default(0);
            $table->decimal('valorsadt', 14, 4)->default(0);
            $table->decimal('anestesia', 14, 4)->nullable();
            $table->decimal('pontosato', 5, 0)->nullable();
            $table->decimal('porteanestesico', 5, 0)->nullable();
            $table->decimal('auxiliares', 1, 0)->nullable();
            $table->decimal('incidencia', 2, 0)->nullable();
            $table->decimal('filme', 6, 4)->nullable();
            $table->decimal('qtdmax', 3, 0)->nullable();
            $table->string('grupo', 8)->nullable();
            $table->decimal('portesala', 1, 0)->nullable();
            $table->char('autorizacao', 1)->nullable();
            $table->char('titgrupo', 1)->nullable();
            $table->decimal('profissional2', 14, 4)->nullable();
            $table->decimal('pofissional3', 14, 4)->nullable();
            $table->string('auxiliar', 8)->nullable();
            $table->char('porteprof', 3)->nullable();
            $table->decimal('multporte', 3, 2)->nullable();
            $table->foreignId('grupos_id')->constrained('grupos');
            $table->foreignId('unidades_id')->constrained('unidades');
            $table->char('pacote', 3)->nullable();
            $table->integer('pacotimp')->default(0);
            $table->char('codigoans', 2)->nullable();
            $table->integer('porte')->nullable();
            $table->integer('equipeimp')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tabelas_itens');
    }
};