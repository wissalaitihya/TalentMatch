<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CompareCandidates;
use App\Ai\Tools\GetCandidateAnalysis;
use App\Ai\Tools\GetJobRequirements;
use App\Models\Analyse;
use App\Models\User;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(2048)]
#[Temperature(0.3)]
#[Timeout(120)]
class AssistantAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    public function __construct(
        private User $user,
        private Analyse $analyse,
    ) {}

    public function instructions(): Stringable|string
    {
        $candidat = $this->analyse->candidat;
        $offre = $this->analyse->offre;

        $nom = $candidat?->nom_candidat ?? 'Inconnu';
        $titre = $offre->titre;
        $analyseId = $this->analyse->id;

        return "Tu es un assistant RH spécialisé dans l'analyse de candidatures. "
            ."Tu travailles sur TalentMatch, un système de présélection automatisé de CV.\n\n"
            ."## Contexte actuel\n"
            ."Tu aides l'agent RH à analyser le candidat **{$nom}** pour l'offre **{$titre}**.\n"
            ."Analyse ID : {$analyseId}\n\n"
            ."## Règle impérative — OBLIGATOIRE\n"
            ."Avant de répondre à TOUTE question sur ce candidat, appelle TOUJOURS l'outil getCandidateAnalysis({$analyseId}) pour obtenir les données réelles depuis la base de données.\n"
            ."Tu dois systématiquement invoquer getCandidateAnalysis({$analyseId}) même si tu penses connaître la réponse.\n"
            ."L'outil te retourne le score, les points forts, les lacunes, les compétences manquantes, la justification, et toutes les autres données sauvegardées.\n\n"
            ."## Règles\n"
            ."- NE JAMAIS inventer des scores, compétences, langues, expérience ou formation.\n"
            ."- Si une information n'est pas disponible dans les données de l'outil, dis-le clairement.\n"
            ."- Tu peux suggérer des questions d'entretien basées sur les lacunes et compétences manquantes retournées par l'outil.\n"
            ."- Tu peux expliquer pourquoi un score a été attribué en utilisant la justification retournée par l'outil.\n"
            .'- Réponds en français.';
    }

    public function tools(): iterable
    {
        return [
            new GetCandidateAnalysis($this->user),
            new GetJobRequirements($this->user),
            new CompareCandidates($this->user),
        ];
    }
}
