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
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Granada, Alhambra, Francia...
            $table->foreignId('pais_id')->nullable()->constrained('paises')->cascadeOnDelete();
            $table->string('tipo')->nullable(); // pais | ciudad | monumento
            $table->timestamps();
            $table->string('wikidata_id')->unique()->nullable(); // Q12345

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keywords');
    }
};
