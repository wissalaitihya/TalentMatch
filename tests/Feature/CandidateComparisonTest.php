<?php

namespace Tests\Feature;

use App\Ai\Tools\CompareCandidates;
use App\Models\Analyse;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

class CandidateComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_show_ranks_completed_scored_candidates_by_score_descending(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Alice']);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Bob']);
        $candidat3 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Charlie']);

        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed', 'matching_score' => 50,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat2->id,
            'statut_analyse' => 'completed', 'matching_score' => 90,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat3->id,
            'statut_analyse' => 'completed', 'matching_score' => 70,
        ]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}");

        $response->assertOk();
        $response->assertSeeInOrder([$candidat2->nom_candidat, $candidat3->nom_candidat, $candidat1->nom_candidat]);
    }

    public function test_candidates_without_score_appear_after_scored_candidates(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Alice']);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Bob']);
        $candidat3 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Charlie']);

        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed', 'matching_score' => 80,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat2->id,
            'statut_analyse' => 'pending', 'matching_score' => null,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat3->id,
            'statut_analyse' => 'failed', 'matching_score' => null,
        ]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}");

        $response->assertOk();
        $response->assertSeeInOrder([$candidat1->nom_candidat, $candidat2->nom_candidat, $candidat3->nom_candidat]);
    }

    public function test_guest_cannot_access_comparison_page(): void
    {
        $offre = Offre::factory()->create();

        $response = $this->get("/offres/{$offre->id}/comparaison");

        $response->assertRedirect('/login');
    }

    public function test_user_cannot_compare_candidates_from_another_users_offer(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}/comparaison");

        $response->assertNotFound();
    }

    public function test_user_cannot_compare_candidates_from_different_offers(): void
    {
        $user = User::factory()->create();
        $offre1 = Offre::factory()->create(['user_id' => $user->id]);
        $offre2 = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre1->id]);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre2->id]);

        Analyse::factory()->create([
            'offre_id' => $offre1->id, 'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed', 'matching_score' => 80,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre2->id, 'candidat_id' => $candidat2->id,
            'statut_analyse' => 'completed', 'matching_score' => 70,
        ]);

        $response = $this->actingAs($user)->post("/offres/{$offre1->id}/comparaison", [
            'candidat_id_1' => $candidat1->id,
            'candidat_id_2' => $candidat2->id,
        ]);

        $response->assertSessionHasErrors('candidat_id_2');
    }

    public function test_user_can_compare_two_completed_analyses_from_own_offer(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Alice']);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Bob']);

        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed', 'matching_score' => 85,
            'points_forts' => ['PHP expert'], 'lacunes' => ['Pas de React'],
            'competences_manquantes' => ['Docker'],
            'justification' => 'Bon profil technique.',
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat2->id,
            'statut_analyse' => 'completed', 'matching_score' => 65,
            'points_forts' => ['React expert'], 'lacunes' => ['Pas de PHP'],
            'competences_manquantes' => ['Laravel'],
            'justification' => 'Profil correct.',
        ]);

        $response = $this->actingAs($user)->post("/offres/{$offre->id}/comparaison", [
            'candidat_id_1' => $candidat1->id,
            'candidat_id_2' => $candidat2->id,
        ]);

        $response->assertOk();
        $response->assertSee('Alice');
        $response->assertSee('Bob');
        $response->assertSee('85%');
        $response->assertSee('65%');
    }

    public function test_comparison_page_displays_both_candidates_saved_data(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Alice']);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Bob']);

        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed', 'matching_score' => 85,
            'competences_extraites' => ['PHP', 'Laravel', 'MySQL'],
            'points_forts' => ['PHP expert', 'Expérience Laravel'],
            'lacunes' => ['Pas de React'],
            'competences_manquantes' => ['Docker', 'AWS'],
            'justification' => 'Excellent profil technique avec une solide expérience Laravel.',
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat2->id,
            'statut_analyse' => 'completed', 'matching_score' => 65,
            'competences_extraites' => ['JavaScript', 'React', 'Node.js'],
            'points_forts' => ['React expert', 'Expérience front-end'],
            'lacunes' => ['Pas de PHP'],
            'competences_manquantes' => ['Laravel'],
            'justification' => 'Bon profil front-end mais manque d\'expérience back-end.',
        ]);

        $response = $this->actingAs($user)->post("/offres/{$offre->id}/comparaison", [
            'candidat_id_1' => $candidat1->id,
            'candidat_id_2' => $candidat2->id,
        ]);

        $response->assertOk();
        $response->assertSee('PHP');
        $response->assertSee('Laravel');
        $response->assertSee('MySQL');
        $response->assertSee('JavaScript');
        $response->assertSee('React');
        $response->assertSee('Node.js');
        $response->assertSee('Pas de React');
        $response->assertSee('Docker');
        $response->assertSee('AWS');
        $response->assertSee('Pas de PHP');
        $response->assertSee('Alice');
        $response->assertSee('Bob');
    }

    public function test_compare_candidates_tool_returns_saved_comparison_data(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Alice']);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Bob']);

        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed', 'matching_score' => 90,
            'points_forts' => ['PHP expert'], 'lacunes' => ['Pas de React'],
            'competences_manquantes' => ['Docker'],
            'justification' => 'Excellent.',
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat2->id,
            'statut_analyse' => 'completed', 'matching_score' => 70,
            'points_forts' => ['React expert'], 'lacunes' => ['Pas de PHP'],
            'competences_manquantes' => ['Laravel'],
            'justification' => 'Correct.',
        ]);

        $tool = new CompareCandidates($user);
        $request = new Request(['candidat_id_1' => $candidat1->id, 'candidat_id_2' => $candidat2->id]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('Alice', $result);
        $this->assertStringContainsString('Bob', $result);
        $this->assertStringContainsString('90', $result);
        $this->assertStringContainsString('70', $result);
        $this->assertStringContainsString('PHP expert', $result);
        $this->assertStringContainsString('React expert', $result);
        $this->assertStringContainsString('supérieur', $result);
    }

    public function test_compare_candidates_tool_refuses_different_offers(): void
    {
        $user = User::factory()->create();
        $offre1 = Offre::factory()->create(['user_id' => $user->id]);
        $offre2 = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre1->id]);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre2->id]);

        Analyse::factory()->create([
            'offre_id' => $offre1->id, 'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed',
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre2->id, 'candidat_id' => $candidat2->id,
            'statut_analyse' => 'completed',
        ]);

        $tool = new CompareCandidates($user);
        $request = new Request(['candidat_id_1' => $candidat1->id, 'candidat_id_2' => $candidat2->id]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('même offre', strtolower($result));
    }

    public function test_compare_candidates_tool_refuses_incomplete_analysis(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id]);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id]);

        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed',
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat2->id,
            'statut_analyse' => 'pending',
        ]);

        $tool = new CompareCandidates($user);
        $request = new Request(['candidat_id_1' => $candidat1->id, 'candidat_id_2' => $candidat2->id]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('terminées', strtolower($result));
    }

    public function test_no_real_ai_call_is_made_during_comparison(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id]);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id]);

        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed', 'matching_score' => 80,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat2->id,
            'statut_analyse' => 'completed', 'matching_score' => 70,
        ]);

        $response = $this->actingAs($user)->post("/offres/{$offre->id}/comparaison", [
            'candidat_id_1' => $candidat1->id,
            'candidat_id_2' => $candidat2->id,
        ]);

        $response->assertOk();
        $this->assertStringNotContainsString('AI', (string) $response->getContent());
    }

    public function test_comparison_requires_two_different_candidates(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);

        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat->id,
            'statut_analyse' => 'completed', 'matching_score' => 80,
        ]);

        $response = $this->actingAs($user)->post("/offres/{$offre->id}/comparaison", [
            'candidat_id_1' => $candidat->id,
            'candidat_id_2' => $candidat->id,
        ]);

        $response->assertSessionHasErrors('candidat_id_2');
    }

    public function test_comparison_form_shows_only_completed_candidates(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Alice']);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Bob']);
        $candidat3 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Charlie']);

        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed', 'matching_score' => 80,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat2->id,
            'statut_analyse' => 'completed', 'matching_score' => 70,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id, 'candidat_id' => $candidat3->id,
            'statut_analyse' => 'failed',
        ]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}/comparaison");

        $response->assertOk();
        $response->assertSee('Alice');
        $response->assertSee('Bob');
        $response->assertDontSee('Charlie');
    }
}
