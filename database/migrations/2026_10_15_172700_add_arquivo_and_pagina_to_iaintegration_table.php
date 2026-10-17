<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('iaintegration', function (Blueprint $table) {
            $table->string('arquivo')->after('id');
            $table->unsignedInteger('pagina')->after('arquivo');
        });
    }

    public function down(): void
    {
        Schema::table('iaintegration', function (Blueprint $table) {
            $table->dropColumn(['arquivo', 'pagina']);
        });
    }
};
