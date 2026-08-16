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
        Schema::create('credenciados_convenios_tabelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credenciado_convenio_id')->constrained('credenciados_convenios');
            $table->foreignId('tabela_padrao_id')->constrained('tabelas');
            $table->foreignId('tabela_medicamentos_id')->constrained('tabelas');
            $table->foreignId('tabela_materiais_id')->constrained('tabelas');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credenciados_convenios_tabelas');
    }
};
