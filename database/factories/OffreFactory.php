<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OffreFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'titre' => fake()->jobTitle(),
            'description' => fake()->paragraphs(3, true),
            'competences_requises' => fake()->randomElements(
                ['PHP', 'Laravel', 'React', 'Vue.js', 'JavaScript', 'Python', 'Docker', 'MySQL', 'Redis', 'AWS'],
                fake()->numberBetween(2, 5)
            ),
            'niveau_experience_min' => fake()->numberBetween(0, 10),
        ];
    }
}
