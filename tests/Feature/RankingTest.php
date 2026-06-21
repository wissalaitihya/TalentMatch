<?php

namespace Tests\Feature;

use App\Models\Analyse;
use App\Models\Candidat;
use App\Models\Offre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_offer_show_page_orders_completed_analyses_by_score_descending(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Alice']);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Bob']);
        $candidat3 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Charlie']);

        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed',
            'matching_score' => 50,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat2->id,
            'statut_analyse' => 'completed',
            'matching_score' => 90,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat3->id,
            'statut_analyse' => 'completed',
            'matching_score' => 70,
        ]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}");

        $response->assertOk();
        $response->assertSeeInOrder([
            'Bob',
            'Charlie',
            'Alice',
        ]);
    }

    public function test_unscored_analyses_appear_after_scored_analyses(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $candidat1 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Scored High']);
        $candidat2 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Pending One']);
        $candidat3 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Scored Low']);
        $candidat4 = Candidat::factory()->create(['offre_id' => $offre->id, 'nom_candidat' => 'Pending Two']);

        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat1->id,
            'statut_analyse' => 'completed',
            'matching_score' => 80,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat2->id,
            'statut_analyse' => 'pending',
            'matching_score' => null,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat3->id,
            'statut_analyse' => 'completed',
            'matching_score' => 60,
        ]);
        Analyse::factory()->create([
            'offre_id' => $offre->id,
            'candidat_id' => $candidat4->id,
            'statut_analyse' => 'pending',
            'matching_score' => null,
        ]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}");

        $response->assertOk();
        $response->assertSeeInOrder([
            'Scored High',
            'Scored Low',
            'Pending One',
            'Pending Two',
        ]);
    }
}
