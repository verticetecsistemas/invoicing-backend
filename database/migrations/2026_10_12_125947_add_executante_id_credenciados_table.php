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
        Schema::table('credenciados', function (Blueprint $table) {
            $table->foreignId('executante_id')->nullable()->constrained('medicos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('credenciados', function (Blueprint $table) {
            $table->dropForeign(['executante_id']);
            $table->dropColumn('executante_id');
        });
    }
};
