<?php

namespace App\Ai\Tools;

use App\Models\Analyse;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetCandidateAnalysis implements Tool
{
    public function __construct(private User $user) {}

    public function description(): Stringable|string
    {
        return 'Retrieve the full structured analysis for a candidate by analyse ID, including name, offer title, score, recommendation, strengths, gaps, missing skills, extracted skills, languages, education, experience, and justification.';
    }

    public function handle(Request $request): Stringable|string
    {
        $analyseId = (int) $request['analyse_id'];

        $analyse = Analyse::with(['candidat', 'offre'])
            ->where('id', $analyseId)
            ->first();

        if (! $analyse) {
            return 'Aucune analyse trouvée pour cette analyse.';
        }

        if ($analyse->offre->user_id !== $this->user->id) {
            return 'Impossible de récupérer les informations de ce candidat.';
        }

        $nomCandidat = $analyse->candidat?->nom_candidat ?? 'Inconnu';
        $titreOffre = $analyse->offre->titre;
        $score = $analyse->matching_score !== null ? $analyse->matching_score.'%' : 'Non disponible';
        $recommandation = $analyse->recommandation?->value ?? 'Non disponible';
        $statut = $analyse->statut_analyse?->value ?? 'Non disponible';
        $experience = $analyse->annees_experience !== null ? $analyse->annees_experience.' ans' : 'Non disponible';
        $etudes = $analyse->niveau_etudes ?? 'Non disponible';
        $competencesExtraites = ! empty($analyse->competences_extraites) ? implode(', ', $analyse->competences_extraites) : 'Non disponible';
        $langues = ! empty($analyse->langues) ? implode(', ', $analyse->langues) : 'Non disponible';
        $pointsForts = ! empty($analyse->points_forts) ? implode(', ', $analyse->points_forts) : 'Non disponible';
        $lacunes = ! empty($analyse->lacunes) ? implode(', ', $analyse->lacunes) : 'Non disponible';
        $competencesManquantes = ! empty($analyse->competences_manquantes) ? implode(', ', $analyse->competences_manquantes) : 'Non disponible';
        $justification = $analyse->justification ?? 'Non disponible';

        return "**Candidat :** {$nomCandidat}\n"
            ."**Offre :** {$titreOffre}\n"
            ."**Statut de l'analyse :** {$statut}\n"
            ."**Score de correspondance :** {$score}\n"
            ."**Recommandation :** {$recommandation}\n"
            ."**Années d'expérience :** {$experience}\n"
            ."**Niveau d'études :** {$etudes}\n"
            ."**Compétences extraites :** {$competencesExtraites}\n"
            ."**Langues :** {$langues}\n"
            ."**Points forts :** {$pointsForts}\n"
            ."**Lacunes :** {$lacunes}\n"
            ."**Compétences manquantes :** {$competencesManquantes}\n"
            ."**Justification :** {$justification}";
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'analyse_id' => $schema->integer()->required()->description('The ID of the analysis to retrieve'),
        ];
    }
}
