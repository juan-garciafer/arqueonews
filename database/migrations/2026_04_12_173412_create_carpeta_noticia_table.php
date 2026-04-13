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
        Schema::create('carpeta_noticia', function (Blueprint $table) {
            $table->foreignId('carpeta_id')
                ->constrained('carpetas')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('noticia_id')
                ->constrained('noticias')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->primary(['carpeta_id', 'noticia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carpeta_noticia');
    }
};
