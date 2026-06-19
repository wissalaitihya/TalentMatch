## 1. Migration & Model

- [x] 1.1 Run `php artisan make:model Offre -m` to create model and migration
- [x] 1.2 Define `offres` table: id, user_id (foreign to users), titre, description, competences_requises (JSON), niveau_experience_minimum (integer), timestamps
- [x] 1.3 Add `array` cast for `competences_requises` field on the Offre model
- [x] 1.4 Add `$fillable` array with all fields
- [x] 1.5 Add `belongsTo(User::class)` relationship on Offre model
- [x] 1.6 Add `hasMany(Offre::class)` relationship on User model
- [x] 1.7 Add `candidats()` relationship placeholder on Offre model (hasMany placeholder — to be implemented later)
- [x] 1.8 Run `php artisan migrate`

## 2. Form Requests

- [x] 2.1 Create `StoreOffreRequest` with validation rules (titre required/string, description required/string, competences_requises required/array|min:1, niveau_experience_minimum required/integer|min:0)
- [x] 2.2 Add `prepareForValidation()` to convert textarea input to array for competences_requises
- [x] 2.3 Set `user_id` from `auth()->id()` in StoreOffreRequest
- [x] 2.4 Create `UpdateOffreRequest` extending StoreOffreRequest with same rules
- [x] 2.5 Add `authorize()` to UpdateOffreRequest checking `$this->offre->user_id === auth()->id()`, returning 404

## 3. Controller & Routes

- [x] 3.1 Create `OffreController` with `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()` methods
- [x] 3.2 Implement `index()`: fetch auth user's offres with `withCount('candidats')`
- [x] 3.3 Implement `create()`: return the create view
- [x] 3.4 Implement `store()`: validate via StoreOffreRequest, create, redirect to index
- [x] 3.5 Implement `show()`: find offre by id, verify ownership in controller, return show view
- [x] 3.6 Implement `edit()`: find offre by id, verify ownership, return edit view
- [x] 3.7 Implement `update()`: validate via UpdateOffreRequest, update, redirect to show
- [x] 3.8 Implement `destroy()`: validate via UpdateOffreRequest (for ownership), delete, redirect to index
- [x] 3.9 Add routes to `routes/web.php` inside `auth` middleware group: `GET /offres`, `GET /offres/create`, `POST /offres`, `GET /offres/{offre}`, `GET /offres/{offre}/edit`, `PUT /offres/{offre}`, `DELETE /offres/{offre}`
- [x] 3.10 Use named routes with `offres.` prefix (offres.index, offres.create, etc.)

## 4. Blade Views

- [x] 4.1 Create `layouts/app.blade.php` override or use existing Breeze layout
- [x] 4.2 Create `index.blade.php` with list table: titre, niveau_experience_minimum, candidats count, actions (voir, modifier, supprimer)
- [x] 4.3 Add empty state when no offers exist
- [x] 4.4 Create `create.blade.php` form with fields: titre, description (textarea), competences_requises (textarea or comma-separated input), niveau_experience_minimum
- [x] 4.5 Add validation error display under each field
- [x] 4.6 Create `show.blade.php` with full offer details
- [x] 4.7 Create `edit.blade.php` pre-filled form reusing create form structure
- [x] 4.8 Add "Mes offres" nav link in Breeze navigation

## 5. Testing

- [x] 5.1 Create `tests/Feature/OffreTest.php`
- [x] 5.2 Test guest redirect for all CRUD routes
- [x] 5.3 Test successful offer creation with valid data
- [x] 5.4 Test offer creation fails with missing required fields
- [x] 5.5 Test offer creation fails with empty competences_requises
- [x] 5.6 Test index returns only authenticated user's offers
- [x] 5.7 Test show returns 404 for another user's offer
- [x] 5.8 Test successful offer update
- [x] 5.9 Test update returns 404 for another user's offer
- [x] 5.10 Test successful offer deletion
- [x] 5.11 Test delete returns 404 for another user's offer
- [x] 5.12 Test competences_requises stored as JSON array
- [x] 5.13 Run `php artisan test --compact --filter=OffreTest` and confirm all pass (10 passed, 20 assertions)

## 6. Polish & Finalize

- [x] 6.1 Run `vendor/bin/pint --format agent` to fix code style
- [x] 6.2 Run full test suite to confirm no regressions (10 passed, 20 assertions)
- [ ] 6.3 Commit with message: "[AI-assisted] Add Offres CRUD — migration, model, controller, Blade views, Form Requests, and Pest tests"
