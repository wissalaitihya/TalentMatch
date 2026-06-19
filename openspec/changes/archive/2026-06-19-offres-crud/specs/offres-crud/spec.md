## ADDED Requirements

### Requirement: Agent RH peut créer une offre
Un agent RH authentifié SHALL pouvoir créer une offre d'emploi avec titre, description, compétences requises et expérience minimum.

#### Scenario: Création réussie
- **WHEN** un agent RH POST `/offres` avec les champs valides
- **THEN** une offre est créée et l'agent est redirigé vers la liste

#### Scenario: Échec avec compétences vides
- **WHEN** un agent RH POST `/offres` avec `competences_requises` vide
- **THEN** la création échoue avec une erreur de validation

### Requirement: Agent RH liste ses propres offres
Un agent RH SHALL voir uniquement ses propres offres avec le nombre de candidatures associées.

#### Scenario: Liste filtrée par utilisateur
- **WHEN** un agent RH GET `/offres`
- **THEN** la réponse contient uniquement les offres où `user_id = auth()->id()`, avec `candidatures_count`

### Requirement: Agent RH voit le détail d'une offre
Un agent RH SHALL pouvoir voir le détail d'une offre, uniquement si elle lui appartient.

#### Scenario: Consultation autorisée
- **WHEN** un agent RH GET `/offres/{id}` où l'offre lui appartient
- **THEN** le détail de l'offre est affiché

#### Scenario: Consultation refusée
- **WHEN** un agent RH GET `/offres/{id}` où l'offre ne lui appartient pas
- **THEN** une erreur 403 ou 404 est retournée

### Requirement: Agent RH peut modifier une offre
Un agent RH SHALL pouvoir modifier une offre qui lui appartient.

#### Scenario: Modification réussie
- **WHEN** un agent RH PUT `/offres/{id}` avec des champs valides
- **THEN** l'offre est mise à jour

### Requirement: Agent RH peut supprimer une offre
Un agent RH SHALL pouvoir supprimer une offre qui lui appartient.

#### Scenario: Suppression réussie
- **WHEN** un agent RH DELETE `/offres/{id}` où l'offre lui appartient
- **THEN** l'offre est supprimée
