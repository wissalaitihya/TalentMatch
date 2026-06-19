<?php

namespace App\Services;

use App\Ai\Agents\AssistantAgent;
use App\Models\Analyse;
use App\Models\User;

class AssistantOrchestrator
{
    public function ask(Analyse $analyse, User $user, string $message): array
    {
        $agent = new AssistantAgent($user, $analyse);

        $analyse->load(['candidat', 'offre']);

        $analyseId = session('assistant_conversation_'.$analyse->id);

        if ($analyseId) {
            $response = $agent->continue($analyseId, as: $user)->prompt($message);
        } else {
            $response = $agent->forUser($user)->prompt($message);
            session()->put('assistant_conversation_'.$analyse->id, $response->conversationId);
        }

        return [
            'response' => $response->text,
            'conversation_id' => $response->conversationId,
        ];
    }
}
