<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tabelas', function (Blueprint $table) {
            $table->string('ans', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tabelas', function (Blueprint $table) {
            $table->string('ans', 2)->nullable(false)->change();
        });
    }
};
