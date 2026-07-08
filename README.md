<p align="center">
    <h1 align="center">TalentMatch AI</h1>
    <p align="center">An intelligent recruitment assistant powered by AI</p>
</p>

---

## The Problem

Recruiters and HR professionals face an overwhelming volume of CVs for every job posting. Manual screening is **time-consuming**, **inconsistent**, and **prone to unconscious bias**. Key candidate information — skills, experience, education — is buried inside unstructured documents, making it difficult to objectively compare applicants and identify the best fit for a role. Without automation, hiring decisions rely on intuition rather than data, leading to missed talent and costly mis-hires.

## The Solution

TalentMatch AI leverages **Laravel AI SDK** with structured output and tool-calling agents to automate and standardize the CV screening process. HR agents create job offers with required criteria, submit candidate CVs, and receive an **AI-powered analysis** that extracts structured insights — skills, experience, education, languages — and computes a **matching score (0–100)** with a clear recommendation: **À convoquer**, **En attente**, or **À rejeter**. A **conversational assistant** enables natural-language queries about candidates, offers, and comparisons, using real database tools rather than guesswork. All analysis runs asynchronously in background queues, keeping the UI responsive.

---

## Features

- **Authentication** — HR agent registration and login via Laravel Breeze
- **Offers CRUD** — Create, read, update, and delete job offers with skills, experience level, education, and language requirements
- **Candidate Submission** — Submit a CV against an offer with structured candidate data
- **AI-Powered CV Analysis** — Background job that extracts structured data from CV text using structured output
- **Matching Score & Recommendation** — Score (0–100) with color-coded recommendation badges
- **Conversational Assistant** — Chat with an AI agent that answers questions using real database tools
- **Candidate Comparison** — Side-by-side comparison with strengths, gaps, and scores
- **Queue-Based Processing** — All AI analysis runs asynchronously via Laravel queues

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 (PHP 8.3) |
| Database | SQLite (default, production-ready alternatives supported) |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Authentication | Laravel Breeze |
| AI SDK | Laravel AI SDK (`laravel/ai`) |
| Queue | Database queue driver |
| Testing | Pest |
| Code Style | Laravel Pint |
| Workflow | OpenSpec (spec-driven development) |

---

## Database Model

```
User 1──N Offre 1──N Candidat 1──1 Analyse
```

| Entity | Description |
|---|---|
| **User** | HR agent with email and password |
| **Offre** | Job offer with title, description, required skills, experience, education, languages |
| **Candidat** | Candidate with name, email, phone, CV text, linked to an offer |
| **Analyse** | Analysis result: extracted skills, experience, education, languages, matching score (0–100), strengths, gaps, missing skills, recommendation (enum), justification, status |
| **ai_conversations / ai_messages** | Conversation memory for the assistant agent (Laravel AI SDK) |

---

## AI Architecture

### Layer 1: Structured CV Analysis

The `CVAnalyzer` agent uses `HasStructuredOutput` to guarantee a type-safe, validated response:

```php
HasStructuredOutput::class
    ::using(CVAnalyzerSchema::class, prompt: $prompt)
```

Extracted fields: `competences_extraites`, `annees_experience`, `niveau_etudes`, `langues`, `matching_score`, `points_forts`, `lacunes`, `competences_manquantes`, `recommandation`, `justification`. Analysis status flows through `pending → processing → completed/failed` with retry-safe job design.

### Layer 2: Conversational Assistant

The `AssistantAgent` uses Laravel AI SDK's tool-calling with `RemembersConversations` for context-aware dialogue. It never invents data — it calls real tools to query the database.

| Tool | Description |
|---|---|
| `getCandidateAnalysis(int $candidatId)` | Returns the full analysis for a candidate |
| `getJobRequirements(int $offreId)` | Returns offer requirements |
| `compareCandidates(int $id1, int $id2)` | Compares two candidates side by side |

---

## Installation

```bash
# 1. Clone the repository
git clone <repository-url>
cd talentmatch-ai

# 2. Install PHP dependencies
composer install

# 3. Install NPM dependencies
npm install

# 4. Copy environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Create SQLite database
touch database/database.sqlite

# 7. Run migrations
php artisan migrate

# 8. Run seeders (optional)
php artisan db:seed

# 9. Build frontend assets
npm run build

# 10. Start the queue worker (in a dedicated terminal)
php artisan queue:work

# 11. Start the development server (in another terminal)
php artisan serve
```

---

## Environment Configuration

```env
APP_NAME=TalentMatch AI
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite

QUEUE_CONNECTION=database
```

Set `AI_API_KEY` in your `.env` file to enable real AI calls. See `config/ai.php` for available provider configurations.

---

## Queue Worker

All CV analysis runs in background jobs (`AnalyzeCandidateJob`) via the database queue driver:

```bash
php artisan queue:work
```

Jobs remain `pending` until the worker picks them up. Monitor the `analyses` table — the `status` field transitions through `pending → processing → completed` (or `failed`).

---

## Testing

```bash
# Run all tests
php artisan test --compact

# Run a specific test file
php artisan test --compact tests/Feature/OffreControllerTest.php

# Run a specific test
php artisan test --compact --filter=test_cv_submission_with_empty_cv_text
```

Tests use **Pest** and are organized under `tests/Feature/` and `tests/Unit/`. AI calls are faked using `CVAnalyzer::fake()` and `AssistantAgent::fake()` — no real API key required.

---

## OpenSpec Workflow

This project follows an **OpenSpec spec-driven development** workflow:

1. **Propose** — A change is proposed with rationale, design, and task breakdown
2. **Apply** — Tasks are implemented by an AI agent
3. **Archive** — Completed changes are archived with delta specs

All commits use the `[AI-assisted]` prefix. Feature branches correspond to specific changes: `feature/offres-crud`, `feature/analyse-ia`, `feature/agent-conversationnel`.

---

## Security

- Authentication required on all routes (Breeze middleware)
- Ownership scoping — users access only their own offers and related data
- All input validated via Form Requests
- No API keys or secrets stored in the codebase
- CV text escaped in Blade views (`{{ }}`)
- `.env` file excluded from Git

---

## Considerations

- Real AI calls require a valid API key in `.env`; tests use fakes
- SQLite is the default driver (swap to MySQL/PostgreSQL for production)
- The assistant is scoped to three defined tools — out-of-scope questions are declined
- CVs are submitted as plain text (file upload not yet implemented)

---

## Project Status

**Complete.** All features implemented and tested (96+ passing tests). Ready for evaluation.

---

<p align="center">
    Built with <a href="https://laravel.com">Laravel</a>, <a href="https://laravel.com/docs/11.x/ai">Laravel AI SDK</a>, and <a href="https://opencode.ai">OpenCode</a>.
</p>
