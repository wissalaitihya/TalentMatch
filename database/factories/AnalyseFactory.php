<?php

namespace Database\Factories;

use App\Models\Candidat;
use App\Models\Offre;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalyseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'offre_id' => Offre::factory(),
            'candidat_id' => Candidat::factory(),
            'statut_analyse' => 'pending',
        ];
    }
}
