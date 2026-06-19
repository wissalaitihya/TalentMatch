<?php

namespace App\Jobs;

use App\Models\Analyse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyzeCandidateJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $analyseId
    ) {}

    public function handle(): void
    {
        $analyse = Analyse::find($this->analyseId);

        if (! $analyse) {
            Log::warning('AnalyzeCandidateJob: Analyse not found', ['id' => $this->analyseId]);

            return;
        }

        $analyse->update(['statut_analyse' => 'processing']);

        $analyse->update([
            'statut_analyse' => 'completed',
            'competences_extraites' => [],
            'annees_experience' => null,
            'niveau_etudes' => null,
            'langues' => [],
            'matching_score' => null,
            'points_forts' => [],
            'lacunes' => [],
            'competences_manquantes' => [],
            'recommandation' => null,
            'justification' => null,
        ]);
    }

    public function failed(\Throwable $e): void
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
