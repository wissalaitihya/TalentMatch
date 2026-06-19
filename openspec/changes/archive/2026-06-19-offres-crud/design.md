## Context

TalentMatch has Breeze authentication installed. Users (RH agents) exist via the default Laravel migration. There is no model or migration yet for job offers. The validated MCD/MLD defines `offres` as a table owned by `users` with fields for title, description, JSON required skills, and minimum experience level. This design covers only the offer management layer — candidate and analysis features come later.

## Goals / Non-Goals

**Goals:**
- Create `Offre` Eloquent model with `offres` migration
- Add `hasMany`/`belongsTo` relationship between User and Offre
- Implement full CRUD via Blade views with Form Request validation
- Scope all offer access to the owning user (return 404 for non-owned offers)
- Store `competences_requises` as JSON array using Eloquent `array` cast
- Add `withCount('candidats')` for candidate counts (placeholder until later feature)
- Cover all routes with Pest feature tests

**Non-Goals:**
- Candidate submission or management
- AI analysis or queue jobs
- Conversational assistant
- Pagination (offers list is small at this stage)
- Public/unauthenticated access to offers

## Decisions

1. **Ownership check via FormRequest `authorize()` instead of Policy**: A Policy is overkill for a single model at this stage. `StoreOffreRequest` sets `user_id` from `auth()->id()`. `UpdateOffreRequest::authorize()` checks `$this->offre->user_id === auth()->id()` and returns 404 on failure (avoids leaking offer existence to unauthorized users). This can be extracted to a Policy later if needed.

2. **404 instead of 403 for unauthorized access**: Returning 404 prevents an attacker from discovering which offer IDs belong to other users. This follows the "don't reveal existence" security principle.

3. **Textarea for required skills input**: The create/edit form uses a textarea where the user enters skills separated by newlines or commas. `StoreOffreRequest::prepareForValidation()` splits and trims the input into an array. This keeps the UX simple without needing a JS tag input library.

4. **`candidats` relationship name**: Uses the existing convention from the validated MCD — `offre hasMany candidats`. The `withCount('candidats')` call will return 0 until the candidates feature is implemented.

5. **No service class for this feature**: CRUD logic is thin enough to live in the controller. Business logic (e.g., AI analysis) will be extracted to Services/Jobs in later features.

6. **Named routes with `offres.` prefix**: Following Laravel resource route naming conventions for clarity and consistency with `route()` helper usage.

## Risks / Trade-offs

- [Textarea-to-array transform] → The `prepareForValidation` logic must handle both string (form POST) and array (API/test) input gracefully. Tests will verify both paths.
- [No pagination] → If an RH agent creates hundreds of offers, the list page will be slow. Pagination should be added before the feature ships to production.
- [No soft deletes] → `destroy()` performs a hard delete. If accidental deletion is a concern, soft deletes can be added later without breaking changes.
