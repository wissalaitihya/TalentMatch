## Why

RH agents need to quickly compare candidates and see them ranked by relevance. Currently, the offer detail page lists candidates but does not sort them by matching score, and there is no comparison view. Adding ranking and comparison enables faster, more informed hiring decisions without leaving the platform or re-running AI analysis.

## What Changes

- **Ranking**: Offer detail page displays analyzed candidates sorted by `matching_score` descending. Completed scored analyses appear first; pending/processing/failed analyses appear after.
- **Comparison**: New comparison page at `/offres/{offre}/comparaison` where an RH agent selects two candidates from the same offer and views a side-by-side comparison of their saved analysis data.
- **Assistant tool update**: `compareCandidates` tool already exists but must be verified to use only saved DB data and enforce ownership.
- **Security**: All new routes are authenticated and scoped to the current user's offers.

## Capabilities

### New Capabilities
- `candidate-ranking`: Display analyzed candidates on offer detail page sorted by matching_score descending, with status badge and recommendation display.
- `candidate-comparison`: Side-by-side comparison view for two candidates from the same offer, using saved analysis data only.

### Modified Capabilities
- `assistant-tools`: The `compareCandidates` tool must work correctly for two candidates from the same offer, use saved database data, enforce ownership, and return safe error messages for invalid comparisons.

## Impact

- `routes/web.php`: Add comparison routes
- `app/Http/Controllers/`: New comparison controller or use existing OfferController
- `app/Models/`: No model changes needed (Offre, Candidat, Analyse already exist)
- `app/Agents/Tools/`: Verify compareCandidates tool implementation
- `resources/views/`: Update offer show view for ranking, create comparison view
- `tests/`: New Pest tests for ranking, comparison, tool enforcement
