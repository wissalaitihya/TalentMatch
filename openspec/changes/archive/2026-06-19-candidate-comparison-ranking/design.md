## Context

The application already has Offres, Candidats, and Analyses models with Eloquent relationships. The offer detail page currently lists candidates. The assistant already has a `compareCandidates` tool placeholder. No comparison UI exists.

## Goals / Non-Goals

**Goals:**
- Rank analyzed candidates by matching_score descending on the offer detail page
- Provide a side-by-side comparison page for two candidates from the same offer
- Ensure the compareCandidates tool enforces same-offre + ownership + completed status
- Reuse existing saved analyses (no new AI calls)

**Non-Goals:**
- Re-running AI CV analysis
- Changing the scoring algorithm
- Export/PDF generation
- Multi-candidate comparison (>2)
- Real-time or WebSocket updates

## Decisions

| Decision | Choice | Rationale |
|---|---|---|
| **Ranking logic** | Controller-level query with `orderByRaw` using CASE expression | Completed scored analyses first (score DESC), then uncompleted ones. Avoids N+1 with eager loading. |
| **Comparison controller** | Thin controller loading two Candidats with Analyse, using a FormRequest for validation | Keeps logic out of controller; reuses existing authorization patterns. |
| **Comparison validation** | Custom FormRequest that checks: same offre, same owner, both analyses completed, analyses exist | Single place for all comparison rules. |
| **compareCandidates tool** | Updated to require same-offre check; returns safe messages for invalid states | Already exists but needs stricter validation per TAL-12. |
| **UI approach** | Blade + Tailwind side-by-side grid | Matches existing simple UI; no JS framework needed. |
| **View data** | Eager load `candidat.analyse` on Offre show; avoid lazy loading | Prevents N+1 queries. |

## Risks / Trade-offs

| Risk | Mitigation |
|---|---|
| Ranked list does not update in real-time after analysis completes | Page refresh reflects latest scores; acceptable for MVP |
| Comparison page loads slowly with large analysis fields | All fields are small JSON arrays; no pagination needed for 2 items |
| Ownership check duplicated in controller and tool | Single policy/gate for ownership; both controller and tool call the same check |
