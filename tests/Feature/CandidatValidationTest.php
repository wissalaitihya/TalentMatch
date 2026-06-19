<?php

namespace Tests\Feature;

use App\Models\Offre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidatValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_cv_is_rejected(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/offres/{$offre->id}/candidats", [
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => '',
        ]);

        $response->assertSessionHasErrors('cv_texte');
    }

    public function test_too_short_cv_is_rejected(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/offres/{$offre->id}/candidats", [
            'nom_candidat' => 'Jean Dupont',
            'cv_texte' => 'Too short',
        ]);

        $response->assertSessionHasErrors('cv_texte');
    }

    public function test_missing_name_is_rejected(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/offres/{$offre->id}/candidats", [
            'nom_candidat' => '',
            'cv_texte' => 'Experienced developer with 5 years in Laravel and PHP.',
        ]);

        $response->assertSessionHasErrors('nom_candidat');
    }
}
