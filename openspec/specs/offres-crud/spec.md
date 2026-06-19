# Offres CRUD

## Purpose

Permettre aux agents RH authentifiés de gérer leurs offres d'emploi (création, consultation, modification, suppression) avant d'y associer des candidatures.

## Requirements

### Requirement: Agent RH peut créer une offre
Un agent RH authentifié SHALL pouvoir créer une offre d'emploi avec titre, description, compétences requises et expérience minimum.

#### Scenario: Création réussie
- **WHEN** un agent RH POST `/offres` avec les champs valides
- **THEN** une offre est créée et l'agent est redirigé vers la liste

#### Scenario: Échec avec champ manquant
- **WHEN** un agent RH POST `/offres` sans le champ `titre`
- **THEN** la création échoue avec une erreur de validation

#### Scenario: Échec avec compétences vides
- **WHEN** un agent RH POST `/offres` avec `competences_requises` vide
- **THEN** la création échoue avec une erreur de validation

#### Scenario: Échec non authentifié
- **WHEN** un invité POST `/offres` avec des champs valides
- **THEN** l'utilisateur est redirigé vers la page de connexion

### Requirement: Agent RH liste ses propres offres
Un agent RH SHALL voir uniquement ses propres offres avec le nombre de candidatures associées.

#### Scenario: Liste filtrée par utilisateur
- **WHEN** un agent RH GET `/offres`
- **THEN** la réponse contient uniquement les offres où `user_id = auth()->id()`, avec `candidats_count`

#### Scenario: Liste vide
- **WHEN** un agent RH sans offre accède à la liste
- **THEN** un message indique qu'aucune offre n'a été créée

### Requirement: Agent RH voit le détail d'une offre
Un agent RH SHALL pouvoir voir le détail d'une offre, uniquement si elle lui appartient.

#### Scenario: Consultation autorisée
- **WHEN** un agent RH GET `/offres/{offre}` où l'offre lui appartient
- **THEN** le détail de l'offre est affiché

#### Scenario: Consultation refusée (offre non trouvée)
- **WHEN** un agent RH GET `/offres/{offre}` où l'offre ne lui appartient pas
- **THEN** une erreur 404 est retournée

### Requirement: Agent RH peut modifier une offre
Un agent RH SHALL pouvoir modifier une offre qui lui appartient.

#### Scenario: Modification réussie
- **WHEN** un agent RH PUT `/offres/{offre}` avec des champs valides
- **THEN** l'offre est mise à jour

#### Scenario: Modification refusée
- **WHEN** un agent RH PUT `/offres/{offre}` où l'offre ne lui appartient pas
- **THEN** une erreur 404 est retournée

### Requirement: Agent RH peut supprimer une offre
Un agent RH SHALL pouvoir supprimer une offre qui lui appartient.

#### Scenario: Suppression réussie
- **WHEN** un agent RH DELETE `/offres/{offre}` où l'offre lui appartient
- **THEN** l'offre est supprimée et l'agent est redirigé vers la liste

#### Scenario: Suppression refusée
- **WHEN** un agent RH DELETE `/offres/{offre}` où l'offre ne lui appartient pas
- **THEN** une erreur 404 est retournée

### Requirement: Compétences requises stockées en JSON
Les compétences requises SHALL être stockées comme un tableau JSON dans la base de données et validées comme un tableau non vide.

#### Scenario: Enregistrement via textarea
- **WHEN** un agent RH soumet une offre avec des compétences séparées par des virgules
- **THEN** les compétences sont converties en tableau JSON et sauvegardées
