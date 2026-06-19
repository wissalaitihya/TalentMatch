<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Candidat extends Model
{
    use HasFactory;

    protected $fillable = [
        'offre_id',
        'nom_candidat',
        'cv_texte',
    ];

    public function offre(): BelongsTo
    {
        return $this->belongsTo(Offre::class);
    }

    public function analyse(): HasOne
    {
        return $this->hasOne(Analyse::class);
    }
}
