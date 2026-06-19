## Why

TalentMatch needs a foundation for job offer management before candidates and AI analysis can be introduced. RH agents must be able to create, view, update, and delete job offers with required skills and minimum experience. Without this, candidates have no offers to be analyzed against.

## What Changes

- New `Offre` Eloquent model with migration (`offres` table)
- `User` hasMany `Offre` / `Offre` belongsTo `User` relationships
- `StoreOffreRequest` and `UpdateOffreRequest` Form Requests with validation
- 7 authenticated Blade CRUD routes (index, create, store, show, edit, update, destroy)
- Ownership scoping: users only see/manage their own offers; 404 returned for unauthorized access
- Required skills stored as JSON array via Eloquent cast
- Offer list displays analyzed candidates count (placeholder 0 until analyse-ia)
- Pest feature tests for all CRUD operations, validation, and ownership rules

## Capabilities

### New Capabilities
- `offres-crud`: Full job offer management for authenticated RH agents — create, list, view, edit, delete offers with title, description, required skills (JSON array), and minimum experience level. Offers are scoped to their owner and include a candidate count.

### Modified Capabilities
None.

## Impact

- **Models**: New `Offre` model; `User` model updated with `offres()` relationship
- **Database**: New `offres` migration with foreign key to `users`
- **Routes**: 7 new routes under `auth` middleware in `routes/web.php`
- **Controllers**: New `OffreController` with full CRUD
- **Views**: New Blade views under `resources/views/offres/`
- **Tests**: New Pest feature test file `tests/Feature/OffreTest.php`
- No existing code is modified beyond the User model and navigation
