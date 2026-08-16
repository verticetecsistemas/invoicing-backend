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
        Schema::create('credenciados_convenios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios');
            $table->foreignId('credenciado_id')->constrained('credenciados');
            $table->decimal('ch_consulta', 10, 4)->nullable();
            $table->decimal('ch_exame', 10, 4)->nullable();
            $table->decimal('ch_procedimento', 10, 4)->nullable();
            $table->decimal('valor_consulta', 10, 2)->nullable();
            $table->string('codigo_convenio')->nullable();
            $table->boolean('autonumeracaoguia')->default(true);
            $table->date('entrega_consulta')->nullable();
            $table->date('entrega_sadt')->nullable();
            $table->date('entrega_honorario')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credenciados_convenios', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
