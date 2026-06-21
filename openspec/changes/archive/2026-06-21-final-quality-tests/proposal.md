## Why

TalentMatch has implemented offres CRUD, candidate submission, AI analysis, conversational assistant, and candidate comparison, but lacks comprehensive test coverage. Without tests, regressions are undetected and the project cannot be confidently demoed or extended. This change hardens the codebase with Pest tests covering authentication, CRUD operations, AI analysis flows, assistant tools, and ranking — fixing any regressions found along the way.

## What Changes

- Add comprehensive Pest feature tests for authentication/security boundaries (guest redirects, ownership enforcement)
- Add Pest tests for Offres CRUD: create, validate, list (own only), view/update/delete (own only), cross-user rejection
- Add Pest tests for candidate submission: valid submission, empty CV rejection, too-short CV rejection, job dispatch, cross-user rejection
- Add Pest tests for structured AI analysis: valid fake AI output, score bounds, invalid recommendation/malformed handling, failed status + error message, JSON array casts
- Add Pest tests for analysis display: completed/failed/pending states, cross-user access denied
- Add Pest tests for assistant tools: getCandidateAnalysis, getJobRequirements, compareCandidates, ownership, missing data safety
- Add Pest tests for ranking/comparison: score-descending order, pending candidates after scored, same-offre enforcement, cross-user rejection
- Fix any regressions or gaps uncovered by the new tests
- Ensure queue/job behavior is testable without real AI API calls
- Run full test suite, verify all pass, apply formatting (Pint)
- No new major features, no MCD/MLD changes, no real AI API calls in tests

## Capabilities

### New Capabilities

No new product capabilities. This change solely concerns test coverage and quality hardening of existing capabilities.

### Modified Capabilities

None. Requirements of existing capabilities (offres-crud, cv-analysis-queue, candidate-submission, assistant-chat, assistant-tools) remain unchanged.

## Impact

- `tests/` directory: new feature test files for each domain area
- `app/` code: minimal fixes only if regressions are found (no business logic rewrites)
- No new migrations, routes, controllers, models, jobs, or views
- No changes to `config/`, `.env`, or dependencies
- CI: full test suite must pass
