## Why

The `AnalyzeCandidateJob` currently uses placeholder values instead of real AI analysis. The `cv-analysis-queue` spec explicitly notes this: "le vrai appel IA sera ajouté dans un changement ultérieur." Without this change, the analysis returns fake data, making the matching score, recommendation, and extracted skills meaningless.

## What Changes

- Create a `CVAnalyzer` agent class implementing `HasStructuredOutput` that extracts structured data from CV text using the Laravel AI SDK
- Update `AnalyzeCandidateJob` to call `CVAnalyzer` instead of using placeholder values, with proper error handling for AI failures
- Add structured output schema mapping (competences_extraites, annees_experience, matching_score, recommandation, etc.)
- Add `Recommandation` backed enum and Eloquent casts for array/attribute fields on the `Analyse` model
- Add guards for empty CV and missing required skills edge cases
- Handle invalid AI responses safely (store as failed)

## Capabilities

### New Capabilities
- `structured-cv-analysis`: CVAnalyzer agent using HasStructuredOutput with a strict JSON schema for extracting skills, experience, education, languages, matching score, strengths, gaps, missing skills, recommendation, and justification from CV text

### Modified Capabilities
- `cv-analysis-queue`: Update AnalyzeCandidateJob to call the real CVAnalyzer agent instead of placeholder values; add status transitions, empty CV guard, invalid AI response handling, and retry-safe error handling

## Impact

- `app/Ai/Agents/CVAnalyzer.php` — new agent file
- `app/Enums/Recommandation.php` — new backed enum
- `app/Jobs/AnalyzeCandidateJob.php` — update to call real AI agent with error handling
- `app/Models/Analyse.php` — add Eloquent casts (array for JSON fields, enum for recommandation)
- No new routes, controllers, views, or migrations
- No changes to composer.json or dependencies
