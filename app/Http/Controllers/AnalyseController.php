<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssistantChatRequest;
use App\Models\Analyse;
use App\Services\AssistantOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AnalyseController extends Controller
{
    public function show(Analyse $analyse): View
    {
        if ($analyse->offre->user_id !== auth()->id()) {
            abort(404);
        }

        $analyse->load(['candidat', 'offre']);

        return view('analyses.show', compact('analyse'));
    }

    public function chat(AssistantChatRequest $request, Analyse $analyse, AssistantOrchestrator $orchestrator): JsonResponse
    {
        if ($analyse->offre->user_id !== $request->user()->id) {
            abort(404);
        }

        $result = $orchestrator->ask(
            analyse: $analyse,
            user: $request->user(),
            message: $request->input('message'),
        );

        return response()->json($result);
    }
}
