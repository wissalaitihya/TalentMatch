<?php

namespace App\Http\Controllers;

use App\Http\Requests\SoumettreCvRequest;
use App\Jobs\AnalyzeCandidateJob;
use App\Models\Offre;
use Illuminate\Http\RedirectResponse;

class CandidatController extends Controller
{
    public function store(SoumettreCvRequest $request, Offre $offre): RedirectResponse
    {
        $candidat = $offre->candidats()->create([
            'nom_candidat' => $request->nom_candidat,
            'cv_texte' => $request->cv_texte,
        ]);

        $analyse = $candidat->analyses()->create([
            'offre_id' => $offre->id,
            'statut_analyse' => 'pending',
        ]);

        AnalyzeCandidateJob::dispatch($analyse->id);

        return redirect()
            ->route('offres.show', $offre)
            ->with('success', 'CV soumis avec succès. L\'analyse a été lancée.');
    }
}
