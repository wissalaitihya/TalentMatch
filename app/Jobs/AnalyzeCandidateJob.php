<?php

namespace App\Jobs;

use App\Ai\Agents\CVAnalyzer;
use App\Enums\Recommandation;
use App\Enums\StatutAnalyse;
use App\Models\Analyse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalyzeCandidateJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(
        public int $analyseId
    ) {}

    public function handle(): void
    {
        $analyse = Analyse::with(['candidat', 'offre'])->find($this->analyseId);

        if (! $analyse || ! $analyse->candidat || ! $analyse->offre) {
            Log::warning('AnalyzeCandidateJob: Analyse, candidat, or offre not found', [
                'id' => $this->analyseId,
            ]);

            return;
        }

        // Skip if already processed
        if ($analyse->statut_analyse === StatutAnalyse::Completed) {
            return;
        }

        $analyse->update(['statut_analyse' => StatutAnalyse::Processing]);

        $cvTexte = $analyse->candidat->cv_texte;

        if (empty(trim($cvTexte))) {
            $analyse->update([
                'statut_analyse' => StatutAnalyse::Failed,
                'message_erreur' => 'Le CV est vide.',
            ]);

            return;
        }

        try {
            $agent = new CVAnalyzer(
                cvTexte: $cvTexte,
                titreOffre: $analyse->offre->titre,
                descriptionOffre: $analyse->offre->description,
                competencesRequises: $analyse->offre->competences_requises ?? [],
            );

            $response = $agent->prompt($cvTexte);

            $data = $response->structured;

            // Validate score
            $score = (int) ($data['matching_score'] ?? 0);
            $score = max(0, min(100, $score));

            // Validate recommendation
            $recommandation = Recommandation::tryFrom($data['recommandation'] ?? '');
            if (! $recommandation) {
                throw new \RuntimeException(
                    'Invalid recommandation: '.json_encode($data['recommandation'] ?? null)
                );
            }

            $analyse->update([
                'statut_analyse' => StatutAnalyse::Completed,
                'competences_extraites' => $data['competences_extraites'] ?? [],
                'annees_experience' => (int) ($data['annees_experience'] ?? 0),
                'niveau_etudes' => $data['niveau_etudes'] ?? '',
                'langues' => $data['langues'] ?? [],
                'matching_score' => $score,
                'points_forts' => $data['points_forts'] ?? [],
                'lacunes' => $data['lacunes'] ?? [],
                'competences_manquantes' => $data['competences_manquantes'] ?? [],
                'recommandation' => $recommandation,
                'justification' => $data['justification'] ?? '',
                'message_erreur' => null,
            ]);

        } catch (Throwable $e) {
            Log::error('AnalyzeCandidateJob failed', [
                'id' => $this->analyseId,
                'error' => $e->getMessage(),
            ]);

            $analyse->update([
                'statut_analyse' => StatutAnalyse::Failed,
                'message_erreur' => $e->getMessage(),
            ]);
        }
    }

    public function failed(Throwable $e): void
    {
        $analyse = Analyse::find($this->analyseId);
        if ($analyse) {
            $analyse->update([
                'statut_analyse' => StatutAnalyse::Failed,
                'message_erreur' => $e->getMessage(),
            ]);
        }
    }
}
