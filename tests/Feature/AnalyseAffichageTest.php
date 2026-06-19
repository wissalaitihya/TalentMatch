<?php

namespace Tests\Feature;

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
}
