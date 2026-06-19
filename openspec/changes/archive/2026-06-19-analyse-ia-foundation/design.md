## Context

TalentMatch Offres CRUD is complete. RH agents can create and manage job offers with required skills and experience levels. The next step is accepting candidate CV submissions against those offers and tracking AI analysis results. This design covers the database, submission flow, and queue foundation — without any real AI integration.

## Goals / Non-Goals

**Goals:**
- Database schema for candidats and analyses_candidats matching the validated MLD
- Eloquent models with casts, relationships, and factories
- Candidate submission from offer show page (authenticated, ownership-gated)
- Form validation (name required, CV text >= 20 chars)
- Background job `AnalyzeCandidateCvJob` dispatched on submission
- Analysis status visible on the offer show page
- Full Pest test coverage

**Non-Goals:**
- Real AI/LLM call (next change)
- Laravel AI SDK structured output (next change)
- Assistant/conversational agent (later change)
- Candidate comparison tools (later change)
- Email notifications

## Decisions

**1. Single analyses_candidats table, not separate analyse table per candidate**
- *Rationale*: Matches the validated MLD where analyse is the pivot with extra data. One row per (offre, candidat) pair. Unique constraint prevents duplicates.

**2. Candidat is a standalone model, not nested under offre**
- *Rationale*: A candidate could theoretically be submitted against multiple offers in the future. Having a standalone `candidats` table with a `hasMany` to analyses makes this extensible.

**3. AnalyseCandidat uses string-based status instead of backed enum**
- *Rationale*: Simpler at this stage. The statut values (pending, processing, completed, failed) are stable. A BackedEnum can be introduced later if needed for type safety.

**4. recommandation field is nullable string, not BackedEnum yet**
- *Rationale*: The Recommendation enum (convoquer, attente, rejeter) is defined in the project standards but since no AI call writes it yet, keeping it nullable string avoids incomplete data. The enum will be introduced when the AI integration fills this field.

**5. AnalyzeCandidateCvJob marks status as completed with placeholder data**
- *Rationale*: The safest approach for testing and demo purposes. On dispatch, the job sets statut_analyse to processing, then immediately to completed with safe placeholder values. This validates the full pipeline (dispatch → queue processing → status update) without real AI. The real AI call will replace this in the next change.

**6. Submission handled via dedicated CandidatController@store, not overloaded on OffreController**
- *Rationale*: Clean separation of concerns. The controller is simple: validate, create candidat, create analyse, dispatch job, redirect back.

**7. Ownership check via Offre model — user can only submit/view their own offers**
- *Rationale*: Follows existing Offre ownership pattern. All candidate operations are scoped through `$offre->user_id === auth()->id()`.

## Risks / Trade-offs

| Risk | Mitigation |
|------|------------|
| Offre show page loads many candidates | Eager load analyses with candidat; paginate if needed later |
| Placeholder job data could be confused with real analysis | Clear status badge (pending/processing/completed) indicates whether AI ran |
| Queue worker not running in dev | Add note in README; job logs warning if queue worker is missing |
| Unique constraint (offre_id, candidat_id) blocks re-submission | Acceptable — a re-submission creates a new candidat record (different name), not same candidate twice |
