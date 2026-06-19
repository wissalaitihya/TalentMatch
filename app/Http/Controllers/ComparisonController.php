<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComparisonRequest;
use App\Models\Analyse;
use App\Models\Offre;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    public function index(Offre $offre): View
    {
        if ($offre->user_id !== auth()->id()) {
            abort(404);
        }

        $offre->load(['analyses.candidat', 'analyses' => function ($q) {
            $q->where('statut_analyse', 'completed')->orderBy('matching_score', 'desc');
        }]);

        return view('offres.comparaison', compact('offre'));
    }

    public function compare(ComparisonRequest $request, Offre $offre): View
    {
        $id1 = (int) $request->input('candidat_id_1');
        $id2 = (int) $request->input('candidat_id_2');

        $analyse1 = Analyse::with('candidat')->where('candidat_id', $id1)->firstOrFail();
        $analyse2 = Analyse::with('candidat')->where('candidat_id', $id2)->firstOrFail();

        $conclusion = $this->generateConclusion($analyse1, $analyse2);

        return view('offres.comparaison', compact('offre', 'analyse1', 'analyse2', 'conclusion'));
    }

    private function generateConclusion(Analyse $a1, Analyse $a2): string
    {
        $nom1 = $a1->candidat?->nom_candidat ?? 'Candidat 1';
        $nom2 = $a2->candidat?->nom_candidat ?? 'Candidat 2';

        $score1 = $a1->matching_score;
        $score2 = $a2->matching_score;

        if ($score1 === null && $score2 === null) {
            return "Aucun des deux candidats n'a de score de correspondance. La comparaison est limitée.";
        }

        if ($score1 === null) {
            return "{$nom2} a un score de {$score2}% alors que {$nom1} n'a pas de score. {$nom2} est mieux positionné pour cette offre.";
        }

        if ($score2 === null) {
            return "{$nom1} a un score de {$score1}% alors que {$nom2} n'a pas de score. {$nom1} est mieux positionné pour cette offre.";
        }

        if ($score1 > $score2) {
            $diff = $score1 - $score2;

            return "{$nom1} (score: {$score1}%) est plus adapté que {$nom2} (score: {$score2}%) avec un écart de {$diff} points pour cette offre.";
        }

        if ($score2 > $score1) {
            $diff = $score2 - $score1;

            return "{$nom2} (score: {$score2}%) est plus adapté que {$nom1} (score: {$score1}%) avec un écart de {$diff} points pour cette offre.";
        }

        return "Les deux candidats ont le même score ({$score1}%). La décision peut se baser sur d'autres critères comme les points forts et les lacunes.";
    }
}
