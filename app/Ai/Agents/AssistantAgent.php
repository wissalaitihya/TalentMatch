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
        $score = $this->analyse->matching_score !== null ? $this->analyse->matching_score.'%' : 'Pas encore calculé';
        $reco = $this->analyse->recommandation?->value ?? 'Pas encore déterminée';
        $statut = $this->analyse->statut_analyse;
        $forces = ! empty($this->analyse->points_forts) ? implode(', ', $this->analyse->points_forts) : 'Pas encore identifiés';
        $lacunes = ! empty($this->analyse->lacunes) ? implode(', ', $this->analyse->lacunes) : 'Pas encore identifiées';
        $manquantes = ! empty($this->analyse->competences_manquantes) ? implode(', ', $this->analyse->competences_manquantes) : 'Aucune';

        return "Tu es un assistant RH spécialisé dans l'analyse de candidatures. "
            ."Tu travailles sur TalentMatch, un système de présélection automatisé de CV.\n\n"
            ."## Contexte actuel\n"
            ."Tu aides l'agent RH à analyser le candidat **{$nom}** pour l'offre **{$titre}**.\n"
            ."- Statut de l'analyse : {$statut}\n"
            ."- Score de correspondance : {$score}\n"
            ."- Recommandation : {$reco}\n"
            ."- Points forts : {$forces}\n"
            ."- Lacunes : {$lacunes}\n"
            ."- Compétences manquantes : {$manquantes}\n\n"
            ."## Règles impératives\n"
            ."- Tu NE DOIS JAMAIS inventer des scores, compétences, langues, expérience ou formation.\n"
            ."- Utilise OBLIGATOIREMENT les outils mis à disposition pour toute question nécessitant des données.\n"
            ."- Si une information n'est pas disponible, dis-le clairement plutôt que d'inventer.\n"
            ."- Les outils retournent des données formatées ; utilise-les pour répondre.\n"
            ."- Tu peux suggérer des questions d'entretien basées sur les lacunes et compétences manquantes.\n"
            ."- Tu peux expliquer pourquoi un score a été attribué en utilisant la justification sauvegardée.\n"
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
