<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offre_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidat_id')->constrained()->cascadeOnDelete();
            $table->string('statut_analyse')->default('pending');
            $table->json('competences_extraites')->nullable();
            $table->unsignedSmallInteger('annees_experience')->nullable();
            $table->string('niveau_etudes')->nullable();
            $table->json('langues')->nullable();
            $table->unsignedTinyInteger('matching_score')->nullable();
            $table->json('points_forts')->nullable();
            $table->json('lacunes')->nullable();
            $table->json('competences_manquantes')->nullable();
            $table->string('recommandation')->nullable();
            $table->text('justification')->nullable();
            $table->text('message_erreur')->nullable();
            $table->timestamps();
            $table->unique(['offre_id', 'candidat_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analyses');
    }
};
