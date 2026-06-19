## 1. Database Migrations

- [x] 1.1 Run `php artisan make:migration create_candidats_table` with columns: id, nom_candidat (string 255), cv_texte (longText), timestamps
- [x] 1.2 Run `php artisan make:migration create_analyses_candidats_table` with columns: id, offre_id (FK→offres cascade), candidat_id (FK→candidats cascade), statut_analyse (string default 'pending'), competences_extraites (json nullable), annees_experience (integer unsigned nullable), niveau_etudes (string nullable), langues (json nullable), matching_score (tinyInteger unsigned nullable), points_forts (json nullable), lacunes (json nullable), competences_manquantes (json nullable), recommandation (string nullable), justification (text nullable), message_erreur (text nullable), timestamps, unique(offre_id, candidat_id)
- [x] 1.3 Run migrations and verify schema

## 2. Models + Factories

- [x] 2.1 Candidat model updated with fillable, casts, and analyses() hasMany relationship
- [x] 2.2 Analyse model updated with fillable, casts (all JSON fields as array, matching_score as integer), belongsTo Offre, belongsTo Candidat
- [x] 2.3 Add hasMany analyses() relationship to existing Offre model
- [x] 2.4 Configure AnalyseFactory with sensible defaults
- [x] 2.5 Configure CandidatFactory with sensible defaults

## 3. Form Request + Controller

- [x] 3.1 Run `php artisan make:request SoumettreCvRequest` with rules: nom_candidat required string max 255, cv_texte required string min 20
- [x] 3.2 Create CandidatController with store method: authorize via offre ownership, validate request, create candidat, create analyse with statut_analyse=pending, dispatch AnalyzeCandidateJob, redirect back with success
- [x] 3.3 Register store route POST /offres/{offre}/candidats (named offres.candidats.store) within auth middleware

## 4. Queue Job

- [x] 4.1 Update AnalyzeCandidateJob with analyseCandidatId constructor param
- [x] 4.2 In handle(): find analyse, set statut_analyse=processing save, set safe placeholder values (empty arrays/0s/null), set statut_analyse=completed save
- [x] 4.3 Add error handling: on failure set statut_analyse=failed and message_erreur

## 5. Views

- [x] 5.1 Add candidate submission form to offer show page: nom_candidat input, cv_texte textarea, submit button
- [x] 5.2 Add candidate analyses list to offer show page: table with name, status badge, score, recommendation, date
- [x] 5.3 Add empty state when no candidates exist
- [x] 5.4 Add validation error display for the form
- [x] 5.5 Style with utility-first Tailwind classes consistent with existing design

## 6. Offer Controller — Eager Loading

- [x] 6.1 Update OffreController show method to eager load analyses with candidats, ordered by created_at desc

## 7. Pest Tests

- [x] 7.1 Create test file for candidate submission: test guest redirect, test valid submission to own offer creates rows with pending status, test cannot submit to another user's offer (403)
- [x] 7.2 Create test file for validation: test empty CV rejected, test too short CV rejected, test missing name rejected
- [x] 7.3 Create test file for display: test show page has empty state, test show page lists candidates, test cannot see another user's candidates
- [x] 7.4 Create test file for analysis/job: test job dispatches on submission, test job transitions pending→processing→completed, test placeholder data is set, test job handles missing analyse gracefully
- [x] 7.5 Run all tests and confirm they pass

## 8. Final Verification

- [x] 8.1 Run `php artisan route:list` to confirm new route is registered
- [x] 8.2 Run `vendor/bin/pint --format agent` to ensure code style compliance
- [x] 8.3 Run full test suite `php artisan test --compact`
