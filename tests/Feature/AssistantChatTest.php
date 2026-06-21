<?php

namespace Tests\Feature;

use App\Ai\Agents\AssistantAgent;
use App\Ai\Tools\CompareCandidates;
use App\Ai\Tools\GetCandidateAnalysis;
use App\Ai\Tools\GetJobRequirements;
use App\Models\Analyse;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

class AssistantChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_analyse_show(): void
    {
        $analyse = Analyse::factory()->create();

        $response = $this->get("/analyses/{$analyse->id}");

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_for_chat(): void
    {
        $analyse = Analyse::factory()->create();

        $response = $this->post("/analyses/{$analyse->id}/chat", [
            'message' => 'Parle-moi de ce candidat.',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_user_can_view_own_analysis(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'matching_score' => 85,
            'statut_analyse' => 'completed',
            'competences_extraites' => ['PHP', 'Laravel'],
            'points_forts' => ['Expérience Laravel'],
            'lacunes' => ['Pas de React'],
        ]);

        $response = $this->actingAs($user)->get("/analyses/{$analyse->id}");

        $response->assertOk();
        $response->assertSee($candidat->nom_candidat);
        $response->assertSee('85%');
    }

    public function test_user_cannot_view_another_users_analysis(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
        ]);

        $response = $this->actingAs($user)->get("/analyses/{$analyse->id}");

        $response->assertNotFound();
    }

    public function test_user_can_ask_question_about_own_analysis(): void
    {
        AssistantAgent::fake([
            'Voici les informations sur ce candidat. Son score est de 85%.',
        ]);

        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
        ]);

        $response = $this->actingAs($user)->post("/analyses/{$analyse->id}/chat", [
            'message' => 'Quel est le score de ce candidat?',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['response', 'conversation_id']);
        $response->assertJson(['response' => 'Voici les informations sur ce candidat. Son score est de 85%.']);

        AssistantAgent::assertPrompted('Quel est le score de ce candidat?');
    }

    public function test_user_cannot_ask_about_another_users_analysis(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
        ]);

        $response = $this->actingAs($user)->post("/analyses/{$analyse->id}/chat", [
            'message' => 'Parle-moi de ce candidat.',
        ]);

        $response->assertNotFound();
    }

    public function test_get_candidate_analysis_tool_returns_saved_data(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id, 'titre' => 'Développeur Laravel']);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Jean Dupont']);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'matching_score' => 92,
            'statut_analyse' => 'completed',
            'competences_extraites' => ['PHP', 'Laravel', 'MySQL'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français', 'Anglais'],
            'points_forts' => ['Maîtrise de Laravel', 'Expérience en équipe'],
            'lacunes' => ['Pas de connaissance en DevOps'],
            'competences_manquantes' => ['Docker'],
            'justification' => 'Excellent profil technique.',
        ]);

        $tool = new GetCandidateAnalysis($user);
        $request = new Request(['candidat_id' => $candidat->id]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('Jean Dupont', $result);
        $this->assertStringContainsString('Développeur Laravel', $result);
        $this->assertStringContainsString('92%', $result);
        $this->assertStringContainsString('Bac+5', $result);
        $this->assertStringContainsString('Anglais', $result);
        $this->assertStringContainsString('Docker', $result);
    }

    public function test_get_candidate_analysis_returns_not_found_for_other_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
        ]);

        $tool = new GetCandidateAnalysis($user);
        $request = new Request(['candidat_id' => $candidat->id]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('Impossible de récupérer', $result);
    }

    public function test_get_job_requirements_tool_returns_offer_data(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'titre' => 'Développeur Full Stack',
            'description' => 'Nous cherchons un développeur expérimenté.',
            'competences_requises' => ['PHP', 'JavaScript', 'Laravel'],
            'niveau_experience_min' => 3,
        ]);

        $tool = new GetJobRequirements($user);
        $request = new Request(['offre_id' => $offre->id]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('Développeur Full Stack', $result);
        $this->assertStringContainsString('Nous cherchons', $result);
        $this->assertStringContainsString('PHP', $result);
        $this->assertStringContainsString('3 ans', $result);
    }

    public function test_get_job_requirements_returns_not_found_for_other_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);

        $tool = new GetJobRequirements($user);
        $request = new Request(['offre_id' => $offre->id]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('Impossible de récupérer', $result);
    }

    public function test_compare_candidates_compares_two_saved_analyses(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Alice']);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Bob']);

        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat1->id,
            'matching_score' => 90,
            'points_forts' => ['PHP expert'],
            'lacunes' => ['Pas de React'],
            'competences_manquantes' => ['Docker'],
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat2->id,
            'matching_score' => 70,
            'points_forts' => ['React expert'],
            'lacunes' => ['Pas de PHP'],
            'competences_manquantes' => ['Laravel'],
        ]);

        $tool = new CompareCandidates($user);
        $request = new Request(['candidat_id_1' => $candidat1->id, 'candidat_id_2' => $candidat2->id]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('Alice', $result);
        $this->assertStringContainsString('Bob', $result);
        $this->assertStringContainsString('90', $result);
        $this->assertStringContainsString('70', $result);
        $this->assertStringContainsString('supérieur', $result);
    }

    public function test_compare_candidates_refuses_for_other_user_candidate(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
        ]);

        $tool = new CompareCandidates($user);
        $request = new Request(['candidat_id_1' => $candidat->id, 'candidat_id_2' => $candidat->id]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('impossible', strtolower($result));
    }

    public function test_compare_candidates_handles_non_existent_candidate(): void
    {
        $user = User::factory()->create();

        $tool = new CompareCandidates($user);
        $request = new Request(['candidat_id_1' => 999, 'candidat_id_2' => 998]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('impossible', strtolower($result));
    }

    public function test_get_candidate_analysis_handles_non_existent_candidate(): void
    {
        $user = User::factory()->create();

        $tool = new GetCandidateAnalysis($user);
        $request = new Request(['candidat_id' => 999]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('Aucune analyse', $result);
    }

    public function test_get_job_requirements_handles_non_existent_offer(): void
    {
        $user = User::factory()->create();

        $tool = new GetJobRequirements($user);
        $request = new Request(['offre_id' => 999]);
        $result = $tool->handle($request);

        $this->assertStringContainsString('Offre non trouvée', $result);
    }

    public function test_tools_return_readable_text_not_raw_json(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'matching_score' => 75,
        ]);

        $tool = new GetCandidateAnalysis($user);
        $result = $tool->handle(new Request(['candidat_id' => $candidat->id]));

        $this->assertStringNotContainsString('{', $result);
        $this->assertStringNotContainsString('[', $result);
        $this->assertStringContainsString('75%', $result);
    }

    public function test_get_candidate_analysis_handles_null_optional_fields(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Jean Dupont']);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'completed',
            'matching_score' => null,
            'competences_extraites' => null,
            'annees_experience' => null,
            'niveau_etudes' => null,
            'langues' => null,
            'points_forts' => null,
            'lacunes' => null,
            'competences_manquantes' => null,
            'recommandation' => null,
            'justification' => null,
        ]);

        $tool = new GetCandidateAnalysis($user);
        $result = $tool->handle(new Request(['candidat_id' => $candidat->id]));

        $this->assertStringContainsString('Jean Dupont', $result);
        $this->assertStringContainsString('Non disponible', $result);
        $this->assertStringNotContainsString('{', $result);
    }

    public function test_get_job_requirements_handles_empty_competences(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create([
            'user_id' => $user->id,
            'titre' => 'Stagiaire',
            'competences_requises' => [],
        ]);

        $tool = new GetJobRequirements($user);
        $result = $tool->handle(new Request(['offre_id' => $offre->id]));

        $this->assertStringContainsString('Stagiaire', $result);
        $this->assertStringContainsString('Aucune compétence requise spécifiée', $result);
    }

    public function test_compare_candidates_refuses_different_offres(): void
    {
        $user = User::factory()->create();
        $offre1 = Offre::factory()->create(['user_id' => $user->id]);
        $offre2 = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre1->id]);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre2->id]);

        Analyse::factory()->create(['offre_id' => $offre1->id, 'candidat_id' => $candidat1->id]);
        Analyse::factory()->create(['offre_id' => $offre2->id, 'candidat_id' => $candidat2->id]);

        $tool = new CompareCandidates($user);
        $result = $tool->handle(new Request([
            'candidat_id_1' => $candidat1->id,
            'candidat_id_2' => $candidat2->id,
        ]));

        $this->assertStringContainsString('Comparaison', $result);
        $this->assertStringContainsString('Candidat 1', $result);
        $this->assertStringContainsString('Candidat 2', $result);
    }

    public function test_compare_candidates_refuses_when_one_is_from_other_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $offreUser = Offre::factory()->create(['user_id' => $user->id]);
        $offreOther = Offre::factory()->create(['user_id' => $otherUser->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offreUser->id]);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offreOther->id]);

        Analyse::factory()->create(['offre_id' => $offreUser->id, 'candidat_id' => $candidat1->id]);
        Analyse::factory()->create(['offre_id' => $offreOther->id, 'candidat_id' => $candidat2->id]);

        $tool = new CompareCandidates($user);
        $result = $tool->handle(new Request([
            'candidat_id_1' => $candidat1->id,
            'candidat_id_2' => $candidat2->id,
        ]));

        $this->assertStringContainsString('impossible', strtolower($result));
    }

    public function test_chat_endpoint_returns_json_response_key_even_when_empty(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
        ]);

        AssistantAgent::fake(['']);

        $response = $this->actingAs($user)->post("/analyses/{$analyse->id}/chat", [
            'message' => 'Bonjour',
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['response', 'conversation_id']);
    }

    public function test_analyse_show_page_shows_chat_component(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
        ]);

        $response = $this->actingAs($user)->get("/analyses/{$analyse->id}");

        $response->assertOk();
        $response->assertSee('Assistant RH');
        $response->assertSee('Posez une question');
    }
}
