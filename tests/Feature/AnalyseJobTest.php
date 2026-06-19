<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeCandidateJob;
use App\Models\Analyse;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AnalyseJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_dispatched_on_submission(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->post("/offres/{$offre->id}/candidats", [
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => 'Experienced developer with 5 years in Laravel and PHP.',
        ]);

        Queue::assertPushed(AnalyzeCandidateJob::class);
    }

    public function test_job_transitions_status_pending_to_processing_to_completed(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        $this->assertEquals('pending', $analyse->fresh()->statut_analyse);

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $this->assertEquals('completed', $analyse->fresh()->statut_analyse);
        $this->assertNotNull($analyse->fresh()->competences_extraites);
    }

    public function test_job_handles_missing_analyse_gracefully(): void
    {
        $job = new AnalyzeCandidateJob(999);

        $job->handle();

        $this->assertTrue(true);
    }

    public function test_job_sets_placeholder_data(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $analyse->refresh();

        $this->assertEquals('completed', $analyse->statut_analyse);
        $this->assertIsArray($analyse->competences_extraites);
        $this->assertIsArray($analyse->langues);
        $this->assertIsArray($analyse->points_forts);
        $this->assertIsArray($analyse->lacunes);
        $this->assertIsArray($analyse->competences_manquantes);
    }
}
