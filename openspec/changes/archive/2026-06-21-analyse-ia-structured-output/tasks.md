## 1. Create Recommandation Enum

- [x] 1.1 Create `app/Enums/Recommandation.php` as a string-backed enum with cases `Convoquer` (`convoquer`), `Attente` (`attente`), `Rejeter` (`rejeter`)

## 2. Add Eloquent Casts to Analyse Model

- [x] 2.1 Add `array` cast for `competences_extraites`, `langues`, `points_forts`, `lacunes`, `competences_manquantes`
- [x] 2.2 Add `integer` cast for `matching_score`
- [x] 2.3 Add `Recommandation` enum cast for `recommandation`

## 3. Create CVAnalyzer Agent

- [x] 3.1 Create `app/Ai/Agents/CVAnalyzer.php` implementing `Agent` and `HasStructuredOutput`
- [x] 3.2 Add `#[UseCheapestModel]` and `#[Temperature(0.1)]` attributes
- [x] 3.3 Add constructor with `cvTexte`, `titreOffre`, `descriptionOffre`, `competencesRequises`
- [x] 3.4 Write `instructions()` method with prompt containing offer details and strict rules
- [x] 3.5 Write `schema()` method returning the full structured output schema (10 fields)

## 4. Update AnalyzeCandidateJob

- [x] 4.1 Update `handle()` to create `CVAnalyzer` with CV text and offer details
- [x] 4.2 Add empty CV guard before AI call
- [x] 4.3 Map AI result to Analyse model fields with `matching_score` clamping
- [x] 4.4 Map `recommandation` string to `Recommandation` backed enum via `match`
- [x] 4.5 Wrap AI call in try/catch and set `failed` status on error
- [x] 4.6 Add `failed()` lifecycle hook for queue-level failures
- [x] 4.7 Handle missing offre/candidat edge case

## 5. Write Tests

- [x] 5.1 Test job dispatches on submission (Queue::fake)
- [x] 5.2 Test job transitions pending → processing → completed with CVAnalyzer::fake
- [x] 5.3 Test job handles empty CV gracefully
- [x] 5.4 Test job clamps matching score to 0-100
- [x] 5.5 Test job maps recommandation to enum correctly
- [x] 5.6 Test invalid AI response sets failed status
- [x] 5.7 Test missing analyse/candidate/offer edge cases
