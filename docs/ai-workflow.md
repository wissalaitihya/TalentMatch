# AI Workflow — TalentMatch

This document explains the two-layer AI architecture powering TalentMatch's CV analysis and conversational assistant.

---

## Overview

TalentMatch uses two distinct AI agents built with the Laravel AI SDK:

1. **CVAnalyzer** (Layer 1) — Structured CV analysis via `HasStructuredOutput`
2. **AssistantAgent** (Layer 2) — Conversational assistant with tool calling

Both agents run asynchronously — analysis in a background queue job, chat via real-time HTTP requests.

---

## Layer 1: CVAnalyzer Agent

### Purpose

Extract structured data from a candidate's CV text and compute a matching score against the job offer requirements.

### File

`app/Ai/Agents/CVAnalyzer.php`

### How it works

```php
#[UseCheapestModel]
#[Temperature(0.1)]
class CVAnalyzer implements Agent, HasStructuredOutput
{
    use Promptable;

    public function schema(JsonSchema $schema): array
    {
        return [
            'competences_extraites' => $schema->array()->items($schema->string())->required(),
            'annees_experience' => $schema->integer()->min(0)->max(60)->required(),
            'niveau_etudes' => $schema->string()->required(),
            'langues' => $schema->array()->items($schema->string())->required(),
            'matching_score' => $schema->integer()->min(0)->max(100)->required(),
            'points_forts' => $schema->array()->items($schema->string())->required(),
            'lacunes' => $schema->array()->items($schema->string())->required(),
            'competences_manquantes' => $schema->array()->items($schema->string())->required(),
            'recommandation' => $schema->string()->enum(['convoquer', 'attente', 'rejeter'])->required(),
            'justification' => $schema->string()->required(),
        ];
    }
}
```

The agent is invoked with `$agent->prompt($cvTexte)`, which guarantees a type-safe, structured JSON response matching the schema. No free-form text is stored.

The prompt includes:
- The CV text
- The job title and description
- The required skills list
- Strict rules: no inventing data, score must be 0–100, recommendation must match score

### Schema Output

| Field | Type | Description |
|---|---|---|
| `competences_extraites` | `string[]` | Skills found in the CV |
| `annees_experience` | `int` | Years of experience |
| `niveau_etudes` | `string` | Education level |
| `langues` | `string[]` | Languages found |
| `matching_score` | `int` (0–100) | Match percentage |
| `points_forts` | `string[]` | Candidate strengths |
| `lacunes` | `string[]` | Gaps relative to the offer |
| `competences_manquantes` | `string[]` | Missing required skills |
| `recommandation` | `enum` | `convoquer`, `attente`, or `rejeter` |
| `justification` | `string` | Explanation of score + recommendation |

### AnalyzeCandidateJob

**File:** `app/Jobs/AnalyzeCandidateJob.php`

The job follows this lifecycle:

```
pending → processing → completed
                       → failed (with error message)
```

1. **Dispatcher** — `CandidatController@store` dispatches `AnalyzeCandidateJob` after a CV is submitted
2. **Processing** — The job reads the analysis, candidat, and offre from DB, sets `statut_analyse = 'processing'`
3. **Empty CV guard** — If CV text is empty, the job fails with `"Le CV est vide."` — no AI call is made
4. **AI Call** — Creates a `CVAnalyzer` instance and calls `prompt()`
5. **Result Mapping** — The structured result is mapped to the `Analyse` model:
   - `matching_score` is clamped to 0–100
   - `recommandation` is mapped to the `Recommandation` backed enum (`convoquer`, `attente`, `rejeter`)
   - Array fields (`competences_extraites`, `langues`, `points_forts`, `lacunes`, `competences_manquantes`) are stored as JSON via Eloquent casts
6. **Error Handling** — Any `Throwable` sets `statut_analyse = 'failed'` and stores the error message
7. **Retry Safety** — The job is retry-safe; the `failed()` method catches exceptions and updates the database

### Testing with Fakes

Tests use `CVAnalyzer::fake()` to avoid real API calls:

```php
CVAnalyzer::fake(fn () => [
    'competences_extraites' => ['PHP', 'Laravel'],
    'annees_experience' => 5,
    'niveau_etudes' => 'Bac+5',
    'langues' => ['Français'],
    'matching_score' => 85,
    'points_forts' => ['Expérience Laravel'],
    'lacunes' => ['Pas de React'],
    'competences_manquantes' => ['Docker'],
    'recommandation' => 'convoquer',
    'justification' => 'Bon profil.',
]);
```

The fake callback receives the prompt string if a `function ($prompt)` is used, allowing tests to verify the prompt content.

---

## Layer 2: AssistantAgent

### Purpose

A conversational RH assistant that answers HR agents' questions about candidates, offers, and comparisons using real database tools.

### File

`app/Ai/Agents/AssistantAgent.php`

### How it works

```php
#[MaxTokens(2048)]
#[Temperature(0.3)]
#[Timeout(120)]
class AssistantAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;
}
```

Key traits and contracts:
- **`Promptable`** — generates the system prompt with current analysis context
- **`RemembersConversations`** — persists conversation history in `ai_conversations` and `ai_messages` tables, scoped to the user via `forUser()`
- **`Conversational`** — enables the `continue()` method for multi-turn dialogue
- **`HasTools`** — provides tool-calling capabilities

### Tools

The agent has access to three real tools (defined in `app/Ai/Tools/`):

| Tool | Description | Parameters |
|---|---|---|
| `GetCandidateAnalysis` | Returns the full structured analysis for a candidate (name, offer, score, recommendation, strengths, gaps, missing skills, etc.) | `candidat_id` (int) |
| `GetJobRequirements` | Returns the job offer requirements (title, description, required skills, minimum experience) | `offre_id` (int) |
| `CompareCandidates` | Compares two candidates' analyses side by side with a comparison summary | `candidat_id_1` (int), `candidat_id_2` (int) |

### Rules

- The agent **must NOT invent information** — it must use tools for all data queries
- Ownership is enforced: each tool checks `user_id` on the offer/analysis before returning data
- The agent can suggest interview questions based on gaps and missing skills
- All responses are in French

### Conversation Memory

Conversations are stored in the `ai_conversations` and `ai_messages` tables (Laravel AI SDK default). The `RemembersConversations` trait handles:
- Creating/continuing conversations via `forUser()`
- Scoping to the specific analysis context
- Persisting the message history for follow-up questions

### Testing with Fakes

Tests use `AssistantAgent::fake()` with predefined responses:

```php
AssistantAgent::fake([
    'Voici les informations sur ce candidat. Son score est de 85%.',
]);
```

The fake returns the given responses in order, allowing tests to verify:
- The chat endpoint returns a valid response
- The `conversation_id` is included in the response
- Ownership and authentication checks work correctly

---

## Queue Worker Requirement

The queue worker **must** be running for CV analysis to complete:

```bash
php artisan queue:work
```

The database queue driver stores jobs in the `jobs` table. Analysis remains `pending` until the worker processes it. The worker does NOT need to be running for the conversational assistant (Layer 2) — chat works via synchronous HTTP requests.

---

## Data Flow Diagram

```
HR Agent submits CV
        │
        ▼
CandidatController@store
        │
        ├── Creates Candidat record
        ├── Creates Analyse record (status: pending)
        └── Dispatches AnalyzeCandidateJob
                │
                ▼
        Queue Worker (php artisan queue:work)
                │
                ├── Sets status: processing
                ├── Validates CV text (not empty)
                ├── Calls CVAnalyzer agent
                │       └── AI returns structured JSON
                ├── Maps result to Analyse model
                └── Sets status: completed (or failed)
                        │
                        ▼
                HR Agent views analysis in UI
                        │
                        ▼
                Assistant Agent (chat)
                        │
                        ├── Uses GetCandidateAnalysis tool
                        ├── Uses GetJobRequirements tool
                        └── Uses CompareCandidates tool
```

---

## Key Design Decisions

1. **Structured output** — Guarantees type-safe, consistent data that maps cleanly to Eloquent attributes with casts
2. **Background queue** — Prevents UI freezes during AI calls (which can take 5–30+ seconds)
3. **Fake in tests** — `CVAnalyzer::fake()` and `AssistantAgent::fake()` avoid real API calls and network dependencies
4. **Tool-based assistant** — Prevents hallucination by forcing the agent to query real database data
5. **Ownership checks** — Each tool verifies `user_id` to prevent data leaks between RH agents
6. **Error recovery** — Failed analyses are visible to the HR agent with the error message; `failed()` lifecycle hook catches queue-level failures
