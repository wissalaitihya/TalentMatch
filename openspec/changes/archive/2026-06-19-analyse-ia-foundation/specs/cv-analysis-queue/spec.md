## ADDED Requirements

### Requirement: AnalyzeCandidateCvJob processes analysis in the background
The system SHALL have an `AnalyzeCandidateCvJob` that processes a candidate's analysis in the background queue. For this foundation change, the job SHALL NOT make a real AI call but SHALL safely update the analysis status through the pipeline.

#### Scenario: Job transitions status from pending to processing to completed
- **WHEN** the `AnalyzeCandidateCvJob` handles an `analyses_candidats` row with `statut_analyse = 'pending'`
- **THEN** the job first updates `statut_analyse` to `'processing'`
- **AND** then updates `statut_analyse` to `'completed'`
- **AND** sets safe placeholder structured values (`competences_extraites`, `annees_experience`, `niveau_etudes`, `langues`, `matching_score`, `points_forts`, `lacunes`, `competences_manquantes`, `recommandation`, `justification`)

#### Scenario: Job handles missing analyse gracefully
- **WHEN** the `AnalyzeCandidateCvJob` is dispatched with a non-existent `analyse_candidat` ID
- **THEN** the job fails gracefully without throwing an unhandled exception

### Requirement: Analysis status is tracked end-to-end
The system SHALL track the analysis lifecycle: `pending` → `processing` → `completed` or `failed`.

#### Scenario: Initial status is pending
- **WHEN** a candidate is first submitted
- **THEN** the `analyses_candidats` row has `statut_analyse = 'pending'`

#### Scenario: Failed status is available for error cases
- **WHEN** the analysis job encounters an error
- **THEN** `statut_analyse` is set to `'failed'` and `message_erreur` stores the error message
