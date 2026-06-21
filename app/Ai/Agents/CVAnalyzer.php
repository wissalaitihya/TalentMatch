<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[UseCheapestModel]
#[Temperature(0.1)]
class CVAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        private string $cvTexte,
        private string $titreOffre,
        private string $descriptionOffre,
        private array $competencesRequises,
    ) {}

    public function instructions(): Stringable|string
    {
        $competences = implode(', ', $this->competencesRequises);

        return "Tu es un assistant RH spécialisé dans l'analyse de CV. "
            ."Analyse le CV fourni par rapport à l'offre d'emploi.\n\n"
            ."## Offre d'emploi\n"
            ."- Titre : {$this->titreOffre}\n"
            ."- Description : {$this->descriptionOffre}\n"
            ."- Compétences requises : {$competences}\n\n"
            ."## Règles impératives\n"
            ."- Ne PAS inventer d'expérience, compétences, langues ou formation.\n"
            ."- Si le CV est peu clair, mentionne-le dans la justification.\n"
            ."- Le score de correspondance doit être entre 0 et 100.\n"
            ."- La recommandation doit correspondre au score et à la justification.\n"
            .'- Réponds uniquement avec la structure JSON demandée.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'competences_extraites' => $schema->array()->items($schema->string())->required()->description('Extracted skills from CV'),
            'annees_experience' => $schema->integer()->min(0)->max(60)->required()->description('Years of experience'),
            'niveau_etudes' => $schema->string()->required()->description('Education level'),
            'langues' => $schema->array()->items($schema->string())->required()->description('Languages found'),
            'matching_score' => $schema->integer()->min(0)->max(100)->required()->description('Match score against offer requirements (0-100)'),
            'points_forts' => $schema->array()->items($schema->string())->required()->description('Candidate strengths'),
            'lacunes' => $schema->array()->items($schema->string())->required()->description('Gaps relative to offer'),
            'competences_manquantes' => $schema->array()->items($schema->string())->required()->description('Missing required skills'),
            'recommandation' => $schema->string()->enum(['convoquer', 'attente', 'rejeter'])->required()->description('Final recommendation'),
            'justification' => $schema->string()->required()->description('Explanation of the score and recommendation'),
        ];
    }
}
