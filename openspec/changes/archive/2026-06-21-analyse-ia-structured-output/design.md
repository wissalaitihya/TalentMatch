## Context

The `AnalyzeCandidateJob` currently sets placeholder values without real AI analysis. The underlying `analyses` table already has all the necessary structured fields. The Laravel AI SDK is installed. The `cv-analysis-queue` spec explicitly deferred the real AI call to this change.

## Goals / Non-Goals

**Goals:**
- Implement `CVAnalyzer` agent with `HasStructuredOutput` returning a strict JSON schema
- Update `AnalyzeCandidateJob` to call `CVAnalyzer` with proper error handling
- Add `Recommandation` backed enum and Eloquent casts on `Analyse` model
- Guard edge cases: empty CV, missing required skills, invalid AI responses
- Allow testing via `CVAnalyzer::fake()`

**Non-Goals:**
- No UI changes, new routes, controllers, or migrations
- No changes to the conversational assistant (Layer 2)
- No provider configuration or API key setup

## Decisions

1. **Agent as a standalone class, not inline** — `CVAnalyzer` is a dedicated class implementing `Agent` and `HasStructuredOutput`, making it testable, faked, and separable from the job
2. **`#[UseCheapestModel]` and `#[Temperature(0.1)]`** — Low temperature for deterministic output; cheapest model keeps costs down for CV analysis
3. **Schema defined via `JsonSchema` builder** — Type-safe schema definition that maps 1:1 to the `analyses` table columns
4. **Result mapped with `max(0, min(100, ...))` clamp** — Protects against out-of-range matching scores from AI
5. **`match` for recommandation mapping** — Simple conversion from string to backed enum with null fallback
6. **Empty CV check before AI call** — Prevents wasting AI API calls on invalid input
7. **`failed()` lifecycle hook** — Catches queue-level failures (e.g., timeout) to update DB status

## Risks / Trade-offs

- [Risk] AI may return invalid or incomplete JSON → Mitigation: `Try/catch` wraps the AI call; caught exceptions set `failed` status with error message
- [Risk] AI may hallucinate skills/experience → Mitigation: Prompt instructions strictly forbid inventing data; justification field documents uncertainty
- [Trade-off] Using cheapest model may reduce analysis quality → Acceptable for initial version; provider can be upgraded later
