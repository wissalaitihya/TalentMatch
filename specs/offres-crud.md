# Offres CRUD

## User Stories

- US2 Create offer
- US3 List offers
- US4 Offer details

## Requirements

An authenticated HR user can:

- create an offer
- edit an offer
- delete an offer
- view offers

## Database

Table: offres

Fields:

- id
- user_id
- titre
- description
- competences_requises
- experience_min

## Validation

titre required

description required

experience_min >= 0

## Constraints

User only sees his own offers

## Acceptance Criteria

Offer can be created
Offer appears in list
Offer details are visible