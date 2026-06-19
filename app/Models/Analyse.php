<?php

namespace App\Models;

use App\Enums\Recommandation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analyse extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidat_id',
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
    ];

    protected $casts = [
        'competences_extraites' => 'array',
        'annees_experience' => 'integer',
        'langues' => 'array',
        'matching_score' => 'integer',
        'points_forts' => 'array',
        'lacunes' => 'array',
        'competences_manquantes' => 'array',
        'recommandation' => Recommandation::class,
    ];

    public function candidat(): BelongsTo
    {
        return $this->belongsTo(Candidat::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
