<?php

namespace Tests\Feature;

use App\Enums\Recommandation;
use App\Models\Analyse;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyseAffichageTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_show_page_shows_empty_state_when_no_candidates(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}");

        $response->assertOk();
        $response->assertSee('Aucun candidat soumis pour cette offre.');
    }

    public function test_offer_show_page_lists_submitted_candidates(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'completed',
            'matching_score' => 85,
        ]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}");

        $response->assertOk();
        $response->assertSee($candidat->nom_candidat);
        $response->assertSee('85%');
        $response->assertSee('Terminé');
    }

    public function test_user_cannot_see_another_users_candidates(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}");

        $response->assertNotFound();
    }

    public function test_completed_analysis_shows_all_details(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id, 'titre' => 'Développeur Laravel']);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Jean Dupont']);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'completed',
            'competences_extraites' => ['PHP', 'Laravel', 'MySQL'],
            'annees_experience' => 5,
            'niveau_etudes' => 'Bac+5',
            'langues' => ['Français', 'Anglais'],
            'matching_score' => 85,
            'points_forts' => ['Maîtrise de Laravel', 'Expérience en équipe'],
            'lacunes' => ['Pas de connaissance en DevOps'],
            'competences_manquantes' => ['Docker'],
            'recommandation' => Recommandation::Convoquer,
            'justification' => 'Excellent profil technique correspondant aux besoins.',
        ]);

        $response = $this->actingAs($user)->get("/analyses/{$analyse->id}");

        $response->assertOk();
        $response->assertSee('Jean Dupont');
        $response->assertSee('Développeur Laravel');
        $response->assertSee('Terminé');
        $response->assertSee('85%');
        $response->assertSee('À convoquer');
        $response->assertSee('Maîtrise de Laravel');
        $response->assertSee('Pas de connaissance en DevOps');
        $response->assertSee('Docker');
        $response->assertSee('Excellent profil technique');
    }

    public function test_failed_analysis_shows_error_message(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'failed',
            'message_erreur' => 'Erreur API: Le service est temporairement indisponible.',
        ]);

        $response = $this->actingAs($user)->get("/analyses/{$analyse->id}");

        $response->assertOk();
        $response->assertSee('Échec');
        $response->assertSee('Erreur API: Le service est temporairement indisponible.');
    }

    public function test_pending_analysis_displays_safely(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'pending',
        ]);

        $response = $this->actingAs($user)->get("/analyses/{$analyse->id}");

        $response->assertOk();
        $response->assertSee('En attente');
        $response->assertDontSee('Erreur');
    }

    public function test_processing_analysis_displays_safely(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'processing',
        ]);

        $response = $this->actingAs($user)->get("/analyses/{$analyse->id}");

        $response->assertOk();
        $response->assertSee('En cours');
    }

    public function test_recommendation_convoquer_has_green_style(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'completed',
            'matching_score' => 85,
            'recommandation' => Recommandation::Convoquer,
        ]);

        $response = $this->actingAs($user)->get("/analyses/{$analyse->id}");

        $response->assertOk();
        $response->assertSee('À convoquer');
        $response->assertSee('green');
    }

    public function test_recommendation_attente_has_orange_style(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'completed',
            'matching_score' => 55,
            'recommandation' => Recommandation::Attente,
        ]);

        $response = $this->actingAs($user)->get("/analyses/{$analyse->id}");

        $response->assertOk();
        $response->assertSee('En attente');
        $response->assertSee('orange');
    }

    public function test_recommendation_rejeter_has_red_style(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);
        $candidat = Candidat::factory()->create(['offre_id' => $offre->id]);
        $analyse = Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat->id,
            'statut_analyse' => 'completed',
            'matching_score' => 25,
            'recommandation' => Recommandation::Rejeter,
        ]);

        $response = $this->actingAs($user)->get("/analyses/{$analyse->id}");

        $response->assertOk();
        $response->assertSee('À rejeter');
        $response->assertSee('red');
    }
}
