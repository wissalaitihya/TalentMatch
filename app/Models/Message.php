<?php

namespace App\Models;

use App\Enums\MessageRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable =
        [
            'analyse_id',
            'role',
            'contenu',
        ];

    protected $casts =
        [
            'role' => MessageRole::class,
        ];

    public function analyse(): BelongsTo
    {
        return $this->belongsTo(Analyse::class);
    }
}
