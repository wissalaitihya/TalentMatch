<?php

namespace App\Ai\Tools;

use App\Models\Offre;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetJobRequirements implements Tool
{
    public function __construct(private User $user) {}

    public function description(): Stringable|string
    {
        return 'Retrieve the job offer requirements including title, description, required skills, and minimum experience level.';
    }

    public function handle(Request $request): Stringable|string
    {
        $offreId = (int) $request['offre_id'];

        $offre = Offre::find($offreId);

        if (! $offre) {
            return 'Offre non trouvée.';
        }

        if ($offre->user_id !== $this->user->id) {
            return 'Impossible de récupérer les informations de cette offre.';
        }

        $competences = ! empty($offre->competences_requises) ? implode(', ', $offre->competences_requises) : 'Aucune compétence requise spécifiée';

        return "**Titre :** {$offre->titre}\n"
            ."**Description :** {$offre->description}\n"
            ."**Compétences requises :** {$competences}\n"
            ."**Expérience minimum :** {$offre->niveau_experience_min} ans";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'offre_id' => $schema->integer()->required()->description('The ID of the job offer to retrieve requirements for'),
        ];
    }
}
