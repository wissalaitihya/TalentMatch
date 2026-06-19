# Candidate Comparison

## Purpose

Permettre aux agents RH de comparer deux candidats d'une même offre côte à côte en utilisant uniquement les données d'analyse sauvegardées, sans rappeler l'IA.

## Requirements

### Requirement: Comparison page at GET /offres/{offre}/comparaison

The system SHALL provide a GET route at `/offres/{offre}/comparaison` that renders a form allowing the RH agent to select two candidates from the same offer for comparison.

#### Scenario: Comparison form renders with candidate selection
- **WHEN** the RH agent accesses the comparison page for their own offer that has at least two candidates
- **THEN** the page SHALL display a form with two select dropdowns listing the candidates, a submit button, and the selected candidates' basic info

#### Scenario: Guest is redirected to login
- **WHEN** an unauthenticated user accesses the comparison page
- **THEN** the system SHALL redirect to the login page

#### Scenario: User cannot access comparison for another user's offer
- **WHEN** an RH agent accesses the comparison page for an offer owned by another user
- **THEN** the system SHALL return a 403 Forbidden response

### Requirement: Comparison POST validates and returns side-by-side view

The system SHALL provide a POST route at `/offres/{offre}/comparaison` that validates the two selected candidates and displays a side-by-side comparison using saved analysis data.

#### Scenario: Successful comparison of two candidates from same offer
- **WHEN** the RH agent selects two candidates from their own offer and both have completed analyses
- **THEN** the system SHALL display both candidates side by side showing: name, score, recommendation (color-coded), extracted skills, strengths, gaps, missing skills, and justification

#### Scenario: Comparison is refused if candidates are from different offers
- **WHEN** the RH agent attempts to compare candidates that belong to different offers
- **THEN** the system SHALL display a validation error and not proceed with the comparison

#### Scenario: Comparison is refused if one analysis is not completed
- **WHEN** the RH agent selects a candidate whose analysis is not in "completed" status
- **THEN** the system SHALL display a validation error indicating the analysis must be completed first

#### Scenario: Comparison shows conclusion based on saved data
- **WHEN** the comparison is displayed
- **THEN** the system SHALL include a conclusion section that identifies the stronger candidate based solely on the saved matching_score and analysis data, without inventing new facts

#### Scenario: Comparison indicates limited data when analysis is incomplete
- **WHEN** a candidate has a completed analysis but some fields are null
- **THEN** the system SHALL display "Non disponible" for missing fields and indicate the comparison is limited
