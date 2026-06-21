## Context

TalentMatch has working feature implementations (offres CRUD, candidate submission, AI analysis via queue, assistant chat with tools, candidate comparison) but near-zero Pest test coverage outside of whatever Breeze scaffolded. The `tests/` directory exists with Pest configured (as per project conventions). Existing factories, routes, controllers, models, jobs, and views are already in place.

Key constraints:
- All tests must use Pest (not PHPUnit)
- No real AI API calls in tests — use fakes/mocks for `laravel/ai` SDK
- Queue must be faked (`Queue::fake()`) to avoid real job dispatch
- Ownership enforcement is via `user_id` on Offre → Candidat → Analyse chain
- Analysis statut flow: `pending` → `processing` → `completed|failed`
- Structured output uses `HasStructuredOutput` trait from laravel/ai SDK

## Goals / Non-Goals

**Goals:**
- Full Pest test suite covering all critical user journeys listed in proposal
- All existing features remain working (no regressions from test-driven fixes)
- Tests run in isolation without external dependencies (no real AI API, no real queue worker)
- Authorization boundaries are enforced and verified at every level
- Edge cases (empty CV, invalid AI responses, missing data) are covered

**Non-Goals:**
- No new features, routes, controllers, models, migrations, or views
- No changes to the MCD/MLD or database schema
- No README, deployment, or CI configuration changes
- No UI redesign or frontend test coverage
- No real AI API integration testing

## Decisions

1. **One test file per domain area** rather than one monolithic file. Each domain (auth, offres, candidats, analyses, assistant, ranking) gets its own file under `tests/Feature/`. Clear naming: `OffreTest.php`, `CandidatTest.php`, `AnalyseTest.php`, `AssistantTest.php`, `RankingTest.php`, `AuthenticationTest.php`.

2. **Use `RefreshDatabase` trait** for all feature tests to ensure clean state between tests.

3. **Use `Queue::fake()`** to prevent actual job dispatch. Assert job was pushed with `Queue::assertPushed()`.

4. **Mock AI structured output** by faking the `Laravel\Ai\Ai` facade or the specific gateway call. The analysis job's `handle()` should be testable by constructing it with known data rather than calling real AI.

5. **Create a second user** in ownership tests using `User::factory()->create()` to verify cross-user access denial.

6. **Test analysis status transitions** by directly manipulating the `statut` column in the database (not relying on real AI job execution).

7. **Helper / trait for common setup** — a `CreatesOffre` or similar pattern in each test to reduce duplication (or use `$this->offre` setup in `beforeEach()`).

8. **No separate test database config** — use in-memory SQLite via `RefreshDatabase` + `.env.testing` or `phpunit.xml` configuration if already set up; otherwise rely on MySQL test database convention.

## Risks / Trade-offs

- [Risk] Mocking AI output in job tests may miss real-world serialization edge cases → Mitigation: test the job constructor + handle with pre-hydrated structured output objects.
- [Risk] Tests that depend on route names may break if routes are renamed → Mitigation: use named route helpers (`route('offres.store')`) consistently.
- [Risk] Queue fake may not catch all job failure paths → Mitigation: test job failure logic directly by invoking the failed() method or catching exceptions in handle().
- [Trade-off] One file per domain means more files but better organization vs a single large test file. Given 7 clear domains, per-file wins.
