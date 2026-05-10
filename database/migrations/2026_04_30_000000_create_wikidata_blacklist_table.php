<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wikidata_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('wikidata_id')->unique(); // Q3329789, etc.
            $table->text('nombre')->nullable(); // Nombre de la entidad
            $table->string('razon')->nullable(); // Razón por la que está en la lista negra
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wikidata_blacklist');
    }
};
