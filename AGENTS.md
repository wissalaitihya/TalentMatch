# TalentMatch

## Rules

Use Laravel conventions.

Use Form Requests.

Use Jobs and Queues.

Use Structured Output.

Use Tool Calling.

Use Conversation Memory.

Use Eloquent Casts.

Avoid N+1 queries.

Use Pest for testing.

Use OpenSpec spec-driven workflow.

Use feature branches: `feature/offres-crud`, `feature/analyse-ia`, `feature/agent-conversationnel`.

No business code before OpenSpec proposal + apply.

No business migrations before MCD/MLD validation.

Commit with "[AI-assisted]" prefix.

=== TalentMatch project rules override the phpunit/core rules below.
TalentMatch uses Pest for all tests, NOT PHPUnit.
Ignore the PHPUnit section in the Laravel Boost guidelines.
Use `php artisan make:test {name}` (without --phpunit) for Pest tests.
Run tests with `php artisan test --compact --filter={testName}`.
===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3
- laravel/ai (AI) - v0
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>

=== talentmatch-project-rules ===

# TalentMatch Project Architecture

## Database / MCD Standards

- Validate MCD/MLD before writing business migrations.
- Core entities: User (RH agent), Offre, Candidat, Analyse.
- Cardinalities: User 1--N Offre, Offre 1--N Candidat, Candidat 1--1 Analyse.
- Define primary keys, foreign keys, nullable fields, indexes, and enum values clearly.
- Conversation memory uses laravel/ai SDK tables (ai_conversations, ai_messages).
- Do not create custom memory tables unless justified.

## Eloquent Casts

Use casts for these fields:
- `competences_requises` → `array`
- `competences_extraites` → `array`
- `langues` → `array`
- `points_forts` → `array`
- `lacunes` → `array`
- `competences_manquantes` → `array`
- `recommandation` → BackedEnum cast
- `matching_score` → `integer`

## Recommendation Enum

Value must use a Laravel BackedEnum:
```php
enum Recommendation: string
{
    case Convoquer = 'convoquer';
    case Attente = 'attente';
    case Rejeter = 'rejeter';
}
```

## AI Structured Output

The CV analysis agent (Layer 1) must implement `HasStructuredOutput` and return this schema:
- `competences_extraites`: string[] — extracted skills from CV
- `annees_experience`: int — years of experience
- `niveau_etudes`: string — education level
- `langues`: string[] — languages found
- `matching_score`: int (0-100) — match against offer requirements
- `points_forts`: string[] — candidate strengths
- `lacunes`: string[] — gaps relative to offer
- `competences_manquantes`: string[] — missing required skills
- `recommandation`: enum convoquer|attente|rejeter — final recommendation
- `justification`: string — explanation of the score and recommendation

## AI Analysis Rules

- Must not return free-form unstructured text for saved analysis.
- Must not invent experience, skills, languages, or education.
- If CV text is unclear, mention uncertainty in justification.
- Matching score must be 0-100.
- Recommendation must match score and justification.
- Invalid AI responses must be handled safely (store as failed).
- Empty CV must be rejected before job dispatch.
- Offer without required skills is an edge case.

## Queue / Job Standards

- Candidate analysis runs in a background job.
- UI must not freeze during analysis.
- Track analysis status: pending, processing, completed, failed.
- Failed AI calls must be visible to the RH agent.
- Jobs should be retry-safe.

## Assistant / Tool Standards

The conversational agent (Layer 2) must use real Laravel tools:
- `getCandidateAnalysis(int $candidatId)` — returns Analyse for candidate
- `getJobRequirements(int $offreId)` — returns Offre details
- `compareCandidates(int $id1, int $id2)` — compares two analyses

Rules:
- Assistant must NOT invent candidate information.
- For candidate questions → use getCandidateAnalysis.
- For offer questions → use getJobRequirements.
- For comparison → use compareCandidates.
- Conversation memory via RemembersConversations trait.
- forUser() scopes conversations to the RH agent.
- continue() resumes existing conversations.

## Security Standards

- Authenticated RH agents only (Breeze auth middleware).
- Ownership checks: users access only their own offers and related data.
- Validate all input via Form Requests.
- Do not expose raw prompts or API keys.
- Do not store secrets in code (use .env).
- Keep .env out of Git.
- Escape CV text safely in Blade ({{ }}).

## Testing Standards (Pest)

- Test auth protection (unauthenticated redirect).
- Test ownership rules (cannot access another user's offer).
- Test offer CRUD (create, read, update, delete).
- Test CV submission validation (empty CV, invalid email, etc.).
- Test analysis status flow.
- Test structured output mapping / casts.
- Edge cases: empty CV, missing required skills, invalid AI response, score outside 0-100, unauthorized access.
- Use fakes/mocks for AI calls, not real API.

## UI Standards (Blade)

- Dashboard: RH agent's offers with candidate count.
- Offer detail: criteria + candidates ordered by matching_score descending.
- Candidate detail: score, recommendation, strengths, gaps, missing skills, justification.
- Recommendation display:
  - score >= 70: "À convoquer" (green)
  - score 40-69: "En attente" (orange)
  - score < 40: "À rejeter" (red)
- Show analysis status badge (pending/processing/completed/failed).
- Keep UI simple and clean with Blade + Tailwind.

## Demo / Explanation Notes

Be ready to explain:
- Why OpenSpec before code — alignment, avoids rework, documents decisions.
- Why queue instead of sync — prevents timeout, retry, non-blocking UX.
- Why structured output — type-safe storage, consistent UI, reliable enum mapping.
- Why tools instead of guessing — prevents hallucination, real DB queries.
- Why memory — follow-up questions, context across conversation.
- What was AI-generated and what was manually reviewed.
