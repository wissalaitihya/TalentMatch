<?php

namespace Tests\Feature;

use App\Models\Offre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OffreTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/offres');
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_offres_create(): void
    {
        $response = $this->get('/offres/create');
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_offres_edit(): void
    {
        $offre = Offre::factory()->create();
        $response = $this->get("/offres/{$offre->id}/edit");
        $response->assertRedirect('/login');
    }

    public function test_user_can_list_their_own_offres(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Offre::factory()->count(3)->create(['user_id' => $user->id]);
        Offre::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get('/offres');

        $response->assertOk();
        $response->assertViewHas('offres', function ($offres) {
            return $offres->count() === 3;
        });
    }

    public function test_user_can_create_an_offre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/offres', [
            'titre' => 'Développeur Laravel',
            'description' => 'Nous recherchons un développeur Laravel expérimenté.',
            'competences_requises' => ['PHP', 'Laravel', 'MySQL'],
            'niveau_experience_min' => 3,
        ]);

        $response->assertRedirect(route('offres.index'));
        $this->assertDatabaseHas('offres', [
            'titre' => 'Développeur Laravel',
            'user_id' => $user->id,
        ]);
    }

    public function test_creation_fails_without_titre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/offres', [
            'titre' => '',
            'description' => 'Description du poste.',
            'competences_requises' => ['PHP'],
            'niveau_experience_min' => 3,
        ]);

        $response->assertSessionHasErrors('titre');
    }

    public function test_creation_fails_without_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/offres', [
            'titre' => 'Développeur Laravel',
            'description' => '',
            'competences_requises' => ['PHP'],
            'niveau_experience_min' => 3,
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_creation_fails_with_non_numeric_experience(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/offres', [
            'titre' => 'Développeur Laravel',
            'description' => 'Description du poste.',
            'competences_requises' => ['PHP'],
            'niveau_experience_min' => 'abc',
        ]);

        $response->assertSessionHasErrors('niveau_experience_min');
    }

    public function test_competences_requises_is_stored_as_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/offres', [
            'titre' => 'Développeur Laravel',
            'description' => 'Description.',
            'competences_requises' => ['PHP', 'Laravel', 'MySQL'],
            'niveau_experience_min' => 3,
        ]);

        $response->assertSessionHasNoErrors();
        $offre = Offre::where('user_id', $user->id)->first();
        $this->assertIsArray($offre->competences_requises);
        $this->assertEquals(['PHP', 'Laravel', 'MySQL'], $offre->competences_requises);
    }

    public function test_user_cannot_access_another_users_offre_edit_page(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}/edit");

        $response->assertNotFound();
    }

    public function test_creation_fails_with_empty_competences(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/offres', [
            'titre' => 'Développeur Laravel',
            'description' => 'Description du poste.',
            'competences_requises' => [],
            'niveau_experience_min' => 3,
        ]);

        $response->assertSessionHasErrors('competences_requises');
    }

    public function test_user_can_see_own_offre_detail(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}");

        $response->assertOk();
        $response->assertSee($offre->titre);
    }

    public function test_user_cannot_see_another_users_offre_detail(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/offres/{$offre->id}");

        $response->assertNotFound();
    }

    public function test_user_can_update_own_offre(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/offres/{$offre->id}", [
            'titre' => 'Titre mis à jour',
            'description' => 'Description mise à jour.',
            'competences_requises' => ['PHP', 'React'],
            'niveau_experience_min' => 5,
        ]);

        $response->assertRedirect(route('offres.show', $offre));
        $this->assertDatabaseHas('offres', [
            'id' => $offre->id,
            'titre' => 'Titre mis à jour',
        ]);
    }

    public function test_user_cannot_update_another_users_offre(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->put("/offres/{$offre->id}", [
            'titre' => 'Titre mis à jour',
            'description' => 'Description mise à jour.',
            'competences_requises' => ['PHP'],
            'niveau_experience_min' => 5,
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_offre(): void
    {
        $user = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/offres/{$offre->id}");

        $response->assertRedirect(route('offres.index'));
        $this->assertDatabaseMissing('offres', ['id' => $offre->id]);
    }

    public function test_user_cannot_delete_another_users_offre(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offre = Offre::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete("/offres/{$offre->id}");

        $response->assertNotFound();
    }
}
