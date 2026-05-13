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
        Schema::create('noticias_visitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('noticia_id')->constrained('noticias')->onDelete('cascade');

            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->string('token_invitado', 64)->nullable();
            $table->string('direccion_ip')->nullable();

            $table->timestamp('visitado_en')->useCurrent();

            // Para listar/mostrar las visitas más recientes de una noticia (orden cronológico)
            $table->index(['noticia_id', 'visitado_en']);

            // Búsqueda rápida: ¿este usuario registrado ya vio esta noticia?
            $table->index(['user_id', 'noticia_id']);

            // Búsqueda rápida: ¿este invitado (por token) ya vio esta noticia?
            $table->index(['token_invitado', 'noticia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noticias_visitas');
    }
};
