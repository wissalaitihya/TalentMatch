<?php

namespace Database\Factories;

use App\Models\Offre;
use Illuminate\Database\Eloquent\Factories\Factory;

class CandidatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'offre_id' => Offre::factory(),
            'nom_candidat' => fake()->name(),
            'cv_texte' => fake()->paragraphs(3, true),
        ];
    }
}
