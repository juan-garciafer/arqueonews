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
        Schema::create('noticias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fuente_id')
                ->nullable()
                ->constrained('fuentes')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('titulo', 255);
            $table->string('descripcion', 500)->nullable();
            $table->string('url_imagen', 255)->nullable();
            $table->string('url_noticia', 255)->unique();
            $table->string('pais', 50);
            $table->dateTime('fecha_publicacion');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }
};
