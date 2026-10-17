<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iaintegration_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iaintegration_id')
                ->constrained('iaintegration')
                ->cascadeOnDelete();
            $table->string('exception_class');
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iaintegration_details');
    }
};
