<?php

namespace Tests\Feature;

use App\Jobs\AnalyzeCandidateJob;
use App\Models\Offre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CandidatSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $offre = Offre::factory()->create();

        $response = $this->post("/offres/{$offre->id}/candidats", [
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => 'Experienced developer with 5 years in Laravel.',
        ]);

        $response->assertRedirect('/login');
    }

    public function test_user_can_submit_cv_to_own_offer(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/offres/{$offre->id}/candidats", [
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => 'Experienced developer with 5 years in Laravel and PHP.',
        ]);

        $response->assertRedirect(route('offres.show', $offre));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('candidats', [
            'offre_id' => $offre->id,
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => 'Experienced developer with 5 years in Laravel and PHP.',
        ]);

        $this->assertDatabaseHas('analyses', [
            'offre_id' => $offre->id,
            'statut_analyse' => 'pending',
        ]);

        Queue::assertPushed(AnalyzeCandidateJob::class);
    }

    public function test_user_cannot_submit_cv_to_another_users_offer(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post("/offres/{$offre->id}/candidats", [
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => 'Experienced developer with 5 years in Laravel and PHP.',
        ]);

        $response->assertForbidden();
    }
}
