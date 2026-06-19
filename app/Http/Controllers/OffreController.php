<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOffreRequest;
use App\Http\Requests\UpdateOffreRequest;
use App\Models\Offre;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OffreController extends Controller
{
    public function index()
    {
        $offres = auth()->user()->offres()->withCount('candidats')->get();

        return view('offres.index', compact('offres'));
    }

    public function create()
    {
        return view('offres.create');
    }

    public function store(StoreOffreRequest $request): RedirectResponse
    {
        $offre = auth()->user()->offres()->create($request->validated());

        return redirect()->route('offres.index')->with('success', 'Offre créée avec succès !');
    }

    public function show(Offre $offre): View
    {
        if ($offre->user_id !== auth()->id()) {
            abort(404);
        }
        $offre->loadCount('candidats');
        $offre->load(['analyses.candidat' => fn ($q) => $q->orderBy('created_at', 'desc')]);

        return view('offres.show', ['offre' => $offre]);
    }

    public function edit(Offre $offre): View
    {
        if ($offre->user_id !== auth()->id()) {
            abort(404);
        }

        return view('offres.edit', compact('offre'));
    }

    public function update(UpdateOffreRequest $request, Offre $offre): RedirectResponse
    {
        $offre->update($request->validated());

        return redirect()
            ->route('offres.show', $offre)
            ->with('success', 'Offre mise à jour avec succès.');
    }

    public function destroy(Offre $offre): RedirectResponse
    {
        if ($offre->user_id !== auth()->id()) {
            abort(404);
        }
        $offre->delete();

        return redirect()->route('offres.index')->with('success', 'Offre supprimée avec succès.');
    }
}
