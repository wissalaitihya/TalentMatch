<?php

namespace App\Models;

use App\Enums\Recommandation;
use App\Enums\StatutAnalyse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analyse extends Model
{
    use HasFactory;

    protected $fillable = [
        'offre_id',
        'candidat_id',
        'statut_analyse',
        'competences_extraites',
        'annees_experience',
        'niveau_etudes',
        'langues',
        'matching_score',
        'points_forts',
        'lacunes',
        'competences_manquantes',
        'recommandation',
        'justification',
        'message_erreur',
    ];

    protected $casts = [
        'statut_analyse' => StatutAnalyse::class,      // ← App\Enums\StatutAnalyse
        'recommandation' => Recommandation::class,     // ← App\Enums\Recommandation
        'competences_extraites' => 'array',
        'langues' => 'array',
        'points_forts' => 'array',
        'lacunes' => 'array',
        'competences_manquantes' => 'array',
        'annees_experience' => 'integer',
        'matching_score' => 'integer',
    ];

    public function offre(): BelongsTo
    {
        return $this->belongsTo(Offre::class);
    }

    public function candidat(): BelongsTo
    {
        return $this->belongsTo(Candidat::class);
    }
}
