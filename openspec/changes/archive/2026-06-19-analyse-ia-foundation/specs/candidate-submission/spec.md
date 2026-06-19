## ADDED Requirements

### Requirement: RH agent can submit a candidate CV against their own offer
The system SHALL allow an authenticated RH agent to submit a candidate's name and CV text from the offer show page. The submission SHALL create a `candidats` row and an `analyses_candidats` row with `statut_analyse = 'pending'`.

#### Scenario: Authenticated user submits valid CV to own offer
- **WHEN** an authenticated user submits a candidate name and CV text (>= 20 chars) on their own offer's show page
- **THEN** a `candidats` row is created with the submitted name and CV text
- **AND** an `analyses_candidats` row is created linked to the offer and candidate with `statut_analyse = 'pending'`
- **AND** the user is redirected back to the offer show page with a success message

#### Scenario: Guest user is redirected to login
- **WHEN** an unauthenticated user tries to submit a candidate CV
- **THEN** the system redirects to the login page

#### Scenario: User cannot submit CV to another user's offer
- **WHEN** an authenticated user tries to submit a candidate CV to an offer owned by another user
- **THEN** the system returns a 403 Forbidden response

#### Scenario: Empty CV text is rejected
- **WHEN** an authenticated user submits a candidate CV with empty CV text
- **THEN** the system shows a validation error for the CV text field

#### Scenario: Too short CV text is rejected
- **WHEN** an authenticated user submits a candidate CV with CV text shorter than 20 characters
- **THEN** the system shows a validation error that the CV text must be at least 20 characters

#### Scenario: Missing candidate name is rejected
- **WHEN** an authenticated user submits a candidate CV without a candidate name
- **THEN** the system shows a validation error for the name field

### Requirement: Offer show page displays submitted candidates with analysis status
The offer show page SHALL display a list of submitted candidates with their name, analysis status, score (if available), recommendation (if available), and creation date.

#### Scenario: Offer has no candidates yet — show empty state
- **WHEN** a user views their offer's show page and no candidates have been submitted
- **THEN** the page shows a clear empty state message indicating no candidates yet

#### Scenario: Offer has candidates — show candidate list
- **WHEN** a user views their offer's show page and candidates exist
- **THEN** each candidate is listed with name, `statut_analyse` badge, `matching_score` (if set), `recommandation` label (if set), and creation date

#### Scenario: User cannot see candidates from another user's offer
- **WHEN** a user navigates to an offer's show page owned by another user
- **THEN** the system returns a 403 Forbidden response

### Requirement: Candidate submission dispatches a background job
The SHALL dispatch a queue job (`AnalyzeCandidateCvJob`) when a candidate is successfully submitted.

#### Scenario: Job is dispatched on submission
- **WHEN** a candidate is successfully submitted against an offer
- **THEN** an `AnalyzeCandidateCvJob` is dispatched with the `analyse_candidat` ID
