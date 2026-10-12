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
        Schema::create('guias', function (Blueprint $table) {
            $table->id();
            $table->string('tipo',1);
            $table->foreignId('credenciado_id')->constrained('credenciados');
            $table->foreignId('convenio_id')->constrained('convenios');                    
            $table->foreignId('paciente_id')->constrained('pacientes');
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('numero',20);
            $table->string('numero_operadora',20);
            $table->date('data');
            $table->date('data_entrega');
            $table->text('observacoes')->nullable();
            $table->string('status', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guias');
    }
};
