# TalentMatch AI

An intelligent recruitment assistant that helps HR agents analyze CVs, compare candidates, and make data-driven hiring decisions using AI-powered analysis and a conversational assistant.

---

## Context

Recruiters face an overwhelming volume of CVs for each job posting. Manually screening candidates is time-consuming, inconsistent, and prone to bias. TalentMatch solves this by leveraging AI to automatically extract structured insights from CVs (skills, experience, education, languages), compute a matching score against job requirements, and provide a conversational interface for natural-language queries about candidates.

## Features

- **Authentication** — HR agent registration and login via Laravel Breeze
- **Offers CRUD** — Create, read, update, and delete job offers with required skills, experience level, education, and languages
- **Candidate Submission** — Submit a CV (name and CV text) against an offer
- **AI-Powered CV Analysis** — Background job that extracts structured data from CV text using the Laravel AI SDK with structured output
- **Matching Score & Recommendation** — Score (0–100) with recommendation: "À convoquer" (score ≥ 70), "En attente" (40–69), "À rejeter" (< 40)
- **Conversational Assistant** — Chat with an AI agent that uses real tools (getCandidateAnalysis, getJobRequirements, compareCandidates) to answer questions
- **Candidate Comparison** — Compare two candidates side by side with strengths, gaps, and scores
- **Queue-Based Processing** — All AI analysis runs in the background via Laravel queues

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 (PHP 8.3) |
| Database | SQLite (default) |
| Frontend | Blade + Tailwind CSS + Alpine.js |
| Authentication | Laravel Breeze |
| AI SDK | Laravel AI SDK (`laravel/ai`) |
| Queue | Database queue driver |
| Testing | Pest |
| Code Style | Laravel Pint |
| Workflow | OpenSpec (spec-driven development) |

## Database Model

```
User 1──N Offre 1──N Candidat 1──1 Analyse
```

- **User** — HR agent (email, password)
- **Offre** — Job offer (title, description, required skills, experience level, education, languages)
- **Candidat** — Candidate (name, email, phone, CV text, offre_id)
- **Analyse** — Analysis result (competences_extraites, annees_experience, niveau_etudes, langues, matching_score, points_forts, lacunes, competences_manquantes, recommandation, justification, status)
- **ai_conversations / ai_messages** — Conversation memory for the assistant agent

## AI Features

### Layer 1: Structured CV Analysis Agent

The `CVAnalyzer` agent uses `HasStructuredOutput` to guarantee a type-safe response:

```php
HasStructuredOutput::class
    ::using(CVAnalyzerSchema::class, prompt: $prompt)
```

It extracts: skills, experience, education, languages, matching score, strengths, gaps, missing skills, recommendation, and justification. The job status flows through `pending → processing → completed/failed` with retry-safe design.

### Layer 2: Conversational Assistant Agent

The `AssistantAgent` uses Laravel AI SDK's tool-calling capabilities with `RemembersConversations` for context-aware dialogue. It never guesses — it calls real tools to query the database.

## Assistant Tools

| Tool | Description |
|---|---|
| `getCandidateAnalysis` | Returns the full analysis for a given candidate |
| `getJobRequirements` | Returns the offer details (required skills, experience, etc.) |
| `compareCandidates` | Compares two candidates side by side from their analyses |

## Queue Worker

CV analysis runs in a background job (`AnalyzeCandidateJob`). The database queue driver stores jobs in the `jobs` table. You must run the queue worker for analysis to complete:

```bash
php artisan queue:work
```

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

# 9. Start the development server
npm run build

# 10. In another terminal, run the queue worker
php artisan queue:work

# 11. In another terminal, start the web server
php artisan serve
```

## Environment Variables

```
APP_NAME=TalentMatch AI
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=talentmatch
# DB_USERNAME=root
# DB_PASSWORD=

QUEUE_CONNECTION=database
```

Set `AI_API_KEY` to your provider's API key for real AI calls. See `config/ai.php` for available providers.

## Running the Queue Worker

```bash
php artisan queue:work
```

Analysis jobs will remain `pending` until the worker processes them. Monitor the `analyses` table: `status` field changes from `pending` → `processing` → `completed` (or `failed`).

## Running Tests

```bash
# Run all tests
php artisan test --compact

# Run a specific test file
php artisan test --compact tests/Feature/OffreControllerTest.php

# Run a specific test
php artisan test --compact --filter=test_cv_submission_with_empty_cv_text
```

Tests use Pest and are organized in `tests/Feature/` and `tests/Unit/`. AI calls are faked in tests using `CVAnalyzer::fake()` and `AssistantAgent::fake()`.

## Demo Scenario

See [docs/demo-scenario.md](docs/demo-scenario.md) for a complete step-by-step walkthrough.

## OpenSpec / AI-Assisted Workflow

This project follows an **OpenSpec spec-driven workflow**:

1. **Propose** — A change is proposed with rationale, design, and tasks
2. **Apply** — The change is implemented task by task by an AI agent
3. **Archive** — Completed changes are archived with the delta spec

All commits use the `[AI-assisted]` prefix. Every feature branch corresponds to a specific change: `feature/offres-crud`, `feature/analyse-ia`, `feature/agent-conversationnel`.

## Security Notes

- Authentication is required for all routes (Breeze middleware)
- Ownership checks ensure users only access their own offers and related data
- All input is validated via Form Requests
- No API keys or secrets are stored in the codebase
- CV text is escaped in Blade views using `{{ }}`
- The `.env` file is excluded from Git

## Known Limitations

- Real AI calls require a valid API key in `.env`; tests use fakes
- SQLite is used by default (not suitable for production scale)
- The assistant only uses the three defined tools — it cannot answer questions outside that scope
- CV text is submitted as plain text (no file upload in the current version)

## Project Status

**Complete.** All features are implemented and tested (96+ passing tests). The project is ready for evaluation.
