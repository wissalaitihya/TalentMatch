<?php

namespace App\Ai\Tools;

use App\Enums\StatutAnalyse;
use App\Models\Analyse;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CompareCandidates implements Tool
{
    public function __construct(private User $user) {}

    public function description(): Stringable|string
    {
        return 'Compare two analyzed candidates using their saved analysis data. Returns both analyses side by side with a comparison summary. Does NOT re-run CV analysis.';
    }

    public function handle(Request $request): Stringable|string
    {
        $id1 = (int) $request['candidat_id_1'];
        $id2 = (int) $request['candidat_id_2'];

        $analyse1 = Analyse::with(['candidat', 'offre'])->where('candidat_id', $id1)->first();
        $analyse2 = Analyse::with(['candidat', 'offre'])->where('candidat_id', $id2)->first();

        if (! $analyse1 || ! $analyse2) {
            return 'Comparaison impossible : un ou les deux candidats sont introuvables.';
        }

        if ($analyse1->offre->user_id !== $this->user->id || $analyse2->offre->user_id !== $this->user->id) {
            return 'Comparaison impossible : vous n\'avez pas accès à l\'un des candidats.';
        }

        if ($analyse1->offre_id !== $analyse2->offre_id) {
            return 'Comparaison impossible : les candidats doivent appartenir à la même offre.';
        }

        if ($analyse1->statut_analyse !== StatutAnalyse::Completed || $analyse2->statut_analyse !== StatutAnalyse::Completed) {
            return 'Comparaison impossible : les deux analyses doivent être terminées.';
        }

        $formatAnalyse = function (Analyse $a) {
            $nom = $a->candidat?->nom_candidat ?? 'Inconnu';
            $score = $a->matching_score !== null ? $a->matching_score.'%' : 'N/D';
            $reco = $a->recommandation?->value ?? 'N/D';
            $exp = $a->annees_experience !== null ? $a->annees_experience.' ans' : 'N/D';
            $etudes = $a->niveau_etudes ?? 'N/D';
            $forces = ! empty($a->points_forts) ? implode(', ', $a->points_forts) : 'N/D';
            $lacunes = ! empty($a->lacunes) ? implode(', ', $a->lacunes) : 'N/D';
            $manquantes = ! empty($a->competences_manquantes) ? implode(', ', $a->competences_manquantes) : 'N/D';

            return "**{$nom}**\n"
                ."- Score : {$score}\n"
                ."- Recommandation : {$reco}\n"
                ."- Expérience : {$exp}\n"
                ."- Études : {$etudes}\n"
                ."- Points forts : {$forces}\n"
                ."- Lacunes : {$lacunes}\n"
                ."- Compétences manquantes : {$manquantes}";
        };

        $score1 = (int) ($analyse1->matching_score ?? 0);
        $score2 = (int) ($analyse2->matching_score ?? 0);
        $diff = abs($score1 - $score2);

        $comparison = "## Analyse du Candidat 1\n\n{$formatAnalyse($analyse1)}\n\n"
            ."## Analyse du Candidat 2\n\n{$formatAnalyse($analyse2)}\n\n"
            ."## Comparaison\n\n";

        if ($score1 > $score2) {
            $comparison .= "Le candidat 1 ({$analyse1->candidat?->nom_candidat}) a un score supérieur de {$diff} points.";
        } elseif ($score2 > $score1) {
            $comparison .= "Le candidat 2 ({$analyse2->candidat?->nom_candidat}) a un score supérieur de {$diff} points.";
        } else {
            $comparison .= "Les deux candidats ont le même score ({$score1}).";
        }

        return $comparison;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'candidat_id_1' => $schema->integer()->required()->description('The ID of the first candidate to compare'),
            'candidat_id_2' => $schema->integer()->required()->description('The ID of the second candidate to compare'),
        ];
    }
}
