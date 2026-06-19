## Why

RH agents need to submit candidate CVs against job offers and track the AI analysis progress. Without this foundation, the AI-powered matching feature cannot be built. This change establishes the database models, submission flow, and background queue infrastructure needed before any AI integration can occur.

## What Changes

- New `candidats` database table and Eloquent model
- New `analyses_candidats` database table and Eloquent model
- Candidate submission form on the offer show page (authenticated RH agents only)
- Form Request validation for CV submission (name required, CV text required min 20 chars)
- Ownership-based authorization (only offer owner can submit/view candidates)
- `AnalyzeCandidateCvJob` queue job (placeholder — no real AI call yet)
- Analysis status tracking: pending → processing → completed/failed
- Display of candidate name, status, score, recommendation, and created date on offer show page
- Pest test coverage for all submission flows, authorization, and validation

## Capabilities

### New Capabilities
- `candidate-submission`: Submit a candidate CV text with name against an offer, create candidat and analyses_candidats rows, dispatch background analysis job
- `cv-analysis-queue`: Background queue job for CV analysis (placeholder only — real AI in next change)

### Modified Capabilities
- *None. Offres CRUD is extended but its existing behavior is unchanged.*

## Impact

- **Database**: 2 new tables (`candidats`, `analyses_candidats`) with foreign keys to `offres`
- **Models**: New `Candidat` and `AnalyseCandidat` Eloquent models with relationships and casts
- **Controllers**: Routes and methods for candidate submission on existing `OffreController` or a new `CandidatController`
- **Jobs**: New `AnalyzeCandidateCvJob` dispatched on submission
- **Views**: Offer show page extended with candidate submission form and candidate analysis list
- **Tests**: Pest feature tests for submission, authorization, validation, and display
- **No new dependencies** — uses built-in Laravel queue, validation, and Blade
