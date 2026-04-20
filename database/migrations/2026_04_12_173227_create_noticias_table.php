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
            $table->text('descripcion')->nullable();

            $table->text('url_imagen')->nullable();
            $table->text('url_noticia');

            $table->string('pais', 75)->nullable();
            $table->dateTime('fecha_publicacion');

            $table->string('external_id', 32)->unique();

            $table->string('hash')->unique()->nullable();

            $table->enum('source', ['feedly', 'rss', 'manual', 'serpapi'])->default('serpapi');


            $table->string('codigo_pais', 2)->nullable();

            $table->string('categoria', 255)->nullable();

            $table->timestamps();

            $table->unique(['source', 'external_id']);
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
