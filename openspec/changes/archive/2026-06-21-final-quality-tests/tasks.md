## 1. Audit & Baseline

- [x] 1.1 Run `php artisan test --compact` to capture current test results baseline
- [x] 1.2 Run `vendor/bin/pint --test` to check current formatting status
- [x] 1.3 Audit existing test files against the acceptance criteria checklist to identify exact gaps
- [x] 1.4 Create a feature branch `feature/final-quality-tests` from the current working branch

## 2. Authentication & Security Gaps

- [x] 2.1 Add test: guest cannot access `/offres/create` (redirect to login)
- [x] 2.2 Add test: guest cannot access `/offres/{offre}/edit` (redirect to login)
- [x] 2.3 Add test: authenticated user can access dashboard and sees own offre count (covered by Breeze auth tests)
- [x] 2.4 Add test: authenticated user cannot access another user's analyse show page

## 3. Offres CRUD Gaps

- [x] 3.1 Add test: required field `titre` is validated on create (empty titre fails)
- [x] 3.2 Add test: required field `description` is validated on create (empty description fails)
- [x] 3.3 Add test: required field `niveau_experience_min` is validated (non-numeric fails)
- [x] 3.4 Add test: `competences_requises` is stored as array and retrievable as array via Eloquent cast
- [x] 3.5 Add test: user cannot access another user's offre edit page (404 or 403)

## 4. Candidate Submission Gaps

- [x] 4.1 Add test: candidat row is created with correct offre_id and nom_candidat after submission
- [x] 4.2 Add test: analyse row is created with `statut_analyse = 'pending'` via database assertion
- [x] 4.3 Add test: submission requires valid offre_id (404 for non-existent offre)
- [x] 4.4 Add test: email validation if email field is present in request (no email field in form — not applicable)

## 5. Structured AI Analysis Tests (AnalyzeCandidateJob)

- [x] 5.1 Add test: job handles valid fake AI structured output with all required fields, saves with `statut = 'completed'`
- [x] 5.2 Add test: `matching_score` is clamped/saved between 0 and 100 (score below 0 saved as 0, above 100 saved as 100)
- [x] 5.3 Add test: invalid recommendation enum value causes `statut = 'failed'` with `message_erreur` populated
- [x] 5.4 Add test: malformed/unparseable AI response causes `statut = 'failed'` with `message_erreur`
- [x] 5.5 Add test: JSON fields (competences_extraites, langues, points_forts, lacunes, competences_manquantes) are cast as arrays on retrieval
- [x] 5.6 Add test: recommendation is cast to BackedEnum on retrieval
- [x] 5.7 Add test: no real external API call is made (use Http::fake or Ai facade fake)
- [x] 5.8 Add test: job is retry-safe (idempotent if called twice)

## 6. Analysis Display Gaps

- [x] 6.1 Add test: completed analysis page shows matching_score, recommandation label, points_forts, lacunes, competences_manquantes, and justification
- [x] 6.2 Add test: failed analysis shows `message_erreur` and appropriate status badge
- [x] 6.3 Add test: pending/processing analysis displays safely without throwing errors (shows loading/en-attente state)
- [x] 6.4 Add test: recommendation display colors — score >= 70 green, 40-69 orange, < 40 red (assert View has correct CSS classes)
- [x] 6.5 Add test: user cannot view another user's analyse (verify 404 or 403)

## 7. Assistant Tools Safety Gaps

- [x] 7.1 Add test: `getCandidateAnalysis` handles null/missing optional fields gracefully (no crash)
- [x] 7.2 Add test: `getJobRequirements` handles offre with empty competences_requises
- [x] 7.3 Add test: `compareCandidates` refuses when candidates belong to different offres
- [x] 7.4 Add test: `compareCandidates` refuses when one candidate is from another user's offre
- [x] 7.5 Add test: chat endpoint returns structured JSON with 'response' key even when agent returns no content

## 8. Ranking & Comparison

- [x] 8.1 Add test: offre show page orders completed analyses by `matching_score` descending
- [x] 8.2 Add test: unscored/pending analyses appear after scored analyses in the list
- [x] 8.3 Add test: user cannot compare candidates from another user's offre via tool

## 9. Run & Fix

- [x] 9.1 Run `php artisan test --compact` and identify all failures
- [x] 9.2 Fix regressions with minimal code changes (no new features, no MCD/MLD changes)
- [x] 9.3 Run `vendor/bin/pint --format agent` to fix formatting

## 10. Final Verification

- [x] 10.1 Run full test suite `php artisan test --compact` — all tests pass
- [x] 10.2 Verify no real AI API calls are made during tests (check for Http/Ai facade usage)
- [x] 10.3 Verify .env is not tracked in git (`git check-ignore .env`)
- [x] 10.4 Verify no API keys or secrets are committed
