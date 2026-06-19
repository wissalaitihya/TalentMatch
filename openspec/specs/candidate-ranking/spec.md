# Candidate Ranking

## Purpose

Classer automatiquement les candidats par score de correspondance sur la page de détail d'une offre pour faciliter la prise de décision de l'agent RH.

## Requirements

### Requirement: Offer detail page ranks candidates by matching score

The system SHALL display analyzed candidates on the offer detail page sorted by matching_score in descending order. Completed analyses with a score SHALL appear first, followed by pending/processing/failed analyses.

#### Scenario: Completed scored candidates appear first sorted by score descending
- **WHEN** the RH agent views an offer detail page that has multiple candidates with completed analyses
- **THEN** the candidates SHALL be displayed ordered by matching_score from highest to lowest

#### Scenario: Unscored candidates appear after scored candidates
- **WHEN** the offer has candidates with completed analyses (scored) and candidates with pending/processing/failed analyses (unscored)
- **THEN** the scored candidates SHALL appear first, followed by the unscored candidates

#### Scenario: Candidate card shows status badge and score
- **WHEN** the offer detail page renders a candidate row
- **THEN** it SHALL display the candidate name, analysis status badge, matching score (if available), recommendation label with color coding, short justification, and a link to the candidate detail/chat

### Requirement: Candidate ranking avoids N+1 queries

The system SHALL eager load all analyses when loading the offer with its candidates to prevent N+1 database queries.

#### Scenario: Offer show page loads candidates with analyses in one query
- **WHEN** the RH agent views an offer detail page
- **THEN** the candidates and their analyses SHALL be loaded using a single eager-loaded query (with() or load()), not lazy-loaded per candidate
