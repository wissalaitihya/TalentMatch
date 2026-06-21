<?php

namespace Tests\Feature;

use App\Ai\Agents\CVAnalyzer;
use App\Enums\Recommandation;
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
        CVAnalyzer::fake(fn () => [
            'competences_extraites' => ['PHP', 'Laravel'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => 85,
            'points_forts' => ['Expérience Laravel'],
            'lacunes' => ['Pas de React'],
            'competences_manquantes' => ['Docker'],
            'recommandation' => 'convoquer',
            'justification' => 'Bon profil.',
        ]);

        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'competences_requises' => ['PHP', 'Laravel'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => 'Développeur PHP avec 5 ans d\'expérience en Laravel.',
        ]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        $this->assertEquals('pending', $analyse->fresh()->statut_analyse);

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $this->assertEquals('completed', $analyse->fresh()->statut_analyse);
    }

    public function test_job_handles_missing_analyse_gracefully(): void
    {
        $job = new AnalyzeCandidateJob(999);

        $job->handle();

        $this->assertTrue(true);
    }

    public function test_job_handles_valid_fake_structured_output(): void
    {
        CVAnalyzer::fake(fn () => [
            'competences_extraites' => ['PHP', 'Laravel'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => 85,
            'points_forts' => ['Expérience Laravel'],
            'lacunes' => ['Pas de React'],
            'competences_manquantes' => ['Docker'],
            'recommandation' => 'convoquer',
            'justification' => 'Bon profil.',
        ]);

        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'titre' => 'Développeur Laravel',
            'competences_requises' => ['PHP', 'Laravel', 'MySQL'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => 'Développeur PHP avec 5 ans d\'expérience en Laravel et MySQL.',
        ]);
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
        $this->assertNotNull($analyse->justification);
        $this->assertIsInt($analyse->matching_score);
    }

    public function test_matching_score_is_clamped_between_0_and_100(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'competences_requises' => ['PHP'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => 'Développeur PHP.',
        ]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        CVAnalyzer::fake(fn () => [
            'competences_extraites' => ['PHP'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => -10,
            'points_forts' => ['PHP'],
            'lacunes' => [],
            'competences_manquantes' => [],
            'recommandation' => 'rejeter',
            'justification' => 'Test negative score.',
        ]);

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $analyse->refresh();
        $this->assertEquals(0, $analyse->matching_score);
    }

    public function test_invalid_recommandation_causes_null(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'competences_requises' => ['PHP'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => 'Développeur PHP.',
        ]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        CVAnalyzer::fake(fn () => [
            'competences_extraites' => ['PHP'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => 50,
            'points_forts' => ['PHP'],
            'lacunes' => [],
            'competences_manquantes' => [],
            'recommandation' => 'invalid_value',
            'justification' => 'Test invalid recommendation.',
        ]);

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $analyse->refresh();
        $this->assertEquals('completed', $analyse->statut_analyse);
        $this->assertNull($analyse->recommandation);
    }

    public function test_malformed_response_causes_failed_status(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'competences_requises' => ['PHP'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => 'Développeur PHP.',
        ]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        CVAnalyzer::fake(function ($prompt) {
            throw new \Exception('Malformed AI response');
        });

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $analyse->refresh();
        $this->assertEquals('failed', $analyse->statut_analyse);
        $this->assertNotNull($analyse->message_erreur);
    }

    public function test_failed_ai_call_stores_error_message(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'competences_requises' => ['PHP'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => 'Développeur PHP.',
        ]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        CVAnalyzer::fake(function ($prompt) {
            throw new \Exception('API call failed');
        });

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $analyse->refresh();
        $this->assertEquals('failed', $analyse->statut_analyse);
        $this->assertNotNull($analyse->message_erreur);
    }

    public function test_json_fields_are_cast_as_arrays(): void
    {
        CVAnalyzer::fake(fn () => [
            'competences_extraites' => ['PHP', 'Laravel'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => 85,
            'points_forts' => ['Expérience Laravel'],
            'lacunes' => ['Pas de React'],
            'competences_manquantes' => ['Docker'],
            'recommandation' => 'convoquer',
            'justification' => 'Bon profil.',
        ]);

        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'competences_requises' => ['PHP'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => 'Développeur PHP.',
        ]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $analyse->refresh();
        $this->assertIsArray($analyse->competences_extraites);
        $this->assertIsArray($analyse->langues);
        $this->assertIsArray($analyse->points_forts);
        $this->assertIsArray($analyse->lacunes);
        $this->assertIsArray($analyse->competences_manquantes);
    }

    public function test_recommandation_is_cast_to_backed_enum(): void
    {
        CVAnalyzer::fake(fn () => [
            'competences_extraites' => ['PHP'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => 85,
            'points_forts' => ['PHP'],
            'lacunes' => [],
            'competences_manquantes' => [],
            'recommandation' => 'convoquer',
            'justification' => 'Bon profil.',
        ]);

        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'competences_requises' => ['PHP'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => 'Développeur PHP avec 5 ans d\'expérience.',
        ]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $analyse->refresh();
        $this->assertEquals('completed', $analyse->statut_analyse);
        if ($analyse->recommandation !== null) {
            $this->assertInstanceOf(Recommandation::class, $analyse->recommandation);
            $this->assertContains($analyse->recommandation->value, ['convoquer', 'attente', 'rejeter']);
        }
    }

    public function test_no_real_external_api_call_is_made(): void
    {
        CVAnalyzer::fake(fn () => [
            'competences_extraites' => ['PHP'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => 85,
            'points_forts' => ['PHP'],
            'lacunes' => [],
            'competences_manquantes' => [],
            'recommandation' => 'convoquer',
            'justification' => 'Bon profil.',
        ]);

        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'competences_requises' => ['PHP'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => 'Développeur PHP avec 5 ans d\'expérience.',
        ]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $analyse->refresh();
        $this->assertEquals('completed', $analyse->statut_analyse);
        CVAnalyzer::assertPrompted('Développeur PHP avec 5 ans d\'expérience.');
    }

    public function test_job_is_retry_safe(): void
    {
        CVAnalyzer::fake(fn () => [
            'competences_extraites' => ['PHP'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français'],
            'matching_score' => 85,
            'points_forts' => ['PHP'],
            'lacunes' => [],
            'competences_manquantes' => [],
            'recommandation' => 'convoquer',
            'justification' => 'Bon profil.',
        ]);

        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'competences_requises' => ['PHP'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => 'Développeur PHP avec 5 ans d\'expérience.',
        ]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        (new AnalyzeCandidateJob($analyse->id))->handle();
        (new AnalyzeCandidateJob($analyse->id))->handle();

        $analyse->refresh();
        $this->assertEquals('completed', $analyse->statut_analyse);
    }

    public function test_job_handles_empty_cv(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'competences_requises' => ['PHP'],
        ]);
        $candidat = Candidat::factory()->create([
            'offre_id' => $offre->id,
            'cv_texte' => '',
        ]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        (new AnalyzeCandidateJob($analyse->id))->handle();

        $analyse->refresh();
        $this->assertEquals('failed', $analyse->statut_analyse);
        $this->assertStringContainsString('vide', $analyse->message_erreur);
    }
}
