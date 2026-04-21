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
        Schema::create('aliases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('keyword_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('nombre');
            $table->timestamps();

            $table->index('nombre');
            $table->index('keyword_id');

            $table->unique(['keyword_id', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aliases');
    }
};
