<?php

namespace App\Jobs;

use App\Ai\Agents\CVAnalyzer;
use App\Enums\Recommandation;
use App\Models\Analyse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalyzeCandidateJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $analyseId
    ) {}

    public function handle(): void
    {
        $analyse = Analyse::with(['candidat', 'offre'])->find($this->analyseId);

        if (! $analyse || ! $analyse->candidat || ! $analyse->offre) {
            Log::warning('AnalyzeCandidateJob: Analyse, candidat, or offre not found', ['id' => $this->analyseId]);

            return;
        }

        $analyse->update(['statut_analyse' => 'processing']);

        $cvTexte = $analyse->candidat->cv_texte;
        $titreOffre = $analyse->offre->titre;
        $descriptionOffre = $analyse->offre->description;
        $competencesRequises = $analyse->offre->competences_requises ?? [];

        if (empty(trim($cvTexte))) {
            $analyse->update([
                'statut_analyse' => 'failed',
                'message_erreur' => 'Le CV est vide.',
            ]);

            return;
        }

        try {
            $agent = new CVAnalyzer(
                cvTexte: $cvTexte,
                titreOffre: $titreOffre,
                descriptionOffre: $descriptionOffre,
                competencesRequises: $competencesRequises,
            );

            $result = $agent->prompt($cvTexte);

            $matchingScore = (int) ($result['matching_score'] ?? 0);
            $matchingScore = max(0, min(100, $matchingScore));

            $recommandationValue = $result['recommandation'] ?? null;
            $recommandation = match ($recommandationValue) {
                'convoquer' => Recommandation::Convoquer,
                'attente' => Recommandation::Attente,
                'rejeter' => Recommandation::Rejeter,
                default => null,
            };

            $analyse->update([
                'statut_analyse' => 'completed',
                'competences_extraites' => $result['competences_extraites'] ?? [],
                'annees_experience' => (int) ($result['annees_experience'] ?? 0),
                'niveau_etudes' => $result['niveau_etudes'] ?? '',
                'langues' => $result['langues'] ?? [],
                'matching_score' => $matchingScore,
                'points_forts' => $result['points_forts'] ?? [],
                'lacunes' => $result['lacunes'] ?? [],
                'competences_manquantes' => $result['competences_manquantes'] ?? [],
                'recommandation' => $recommandation,
                'justification' => $result['justification'] ?? '',
            ]);
        } catch (Throwable $e) {
            Log::error('AnalyzeCandidateJob failed', [
                'id' => $this->analyseId,
                'error' => $e->getMessage(),
            ]);

            $analyse->update([
                'statut_analyse' => 'failed',
                'message_erreur' => $e->getMessage(),
            ]);
        }
    }

    public function failed(Throwable $e): void
    {
        $analyse = Analyse::find($this->analyseId);
        if ($analyse) {
            $analyse->update([
                'statut_analyse' => 'failed',
                'message_erreur' => $e->getMessage(),
            ]);
        }
    }
}
