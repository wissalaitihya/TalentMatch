## MODIFIED Requirements

### Requirement: AnalyzeCandidateJob processes analysis in the background
The system SHALL have an `AnalyzeCandidateJob` that processes a candidate's analysis in the background queue. The job SHALL call the real `CVAnalyzer` agent with structured output. The job SHALL safely handle AI failures, empty CVs, and missing required skills.

#### Scenario: Job calls real CVAnalyzer agent instead of placeholder
- **WHEN** the `AnalyzeCandidateJob` handles an `analyses` row with `statut_analyse = 'pending'`
- **THEN** the job first updates `statut_analyse` to `'processing'`
- **AND** the job creates a `CVAnalyzer` with the CV text, offer title, description, and required skills
- **AND** the job calls the agent's `prompt()` method
- **AND** maps the structured result to the `analyses` row fields
- **AND** updates `statut_analyse` to `'completed'`

#### Scenario: Job maps recommandation string to backed enum
- **WHEN** the `CVAnalyzer` returns a `recommandation` string value
- **THEN** the job maps `'convoquer'` to `Recommandation::Convoquer`, `'attente'` to `Recommandation::Attente`, `'rejeter'` to `Recommandation::Rejeter`
- **AND** stores the enum on the `analyses` row

#### Scenario: Job clamps matching score to 0-100 range
- **WHEN** the `CVAnalyzer` returns a `matching_score` outside the 0-100 range
- **THEN** the job clamps the value using `max(0, min(100, $score))`
- **AND** stores the clamped value

#### Scenario: Job handles empty CV gracefully
- **WHEN** the CV text for a candidate is empty or whitespace-only
- **THEN** the job sets `statut_analyse = 'failed'` and `message_erreur = 'Le CV est vide.'`
- **AND** does NOT call the AI agent

#### Scenario: Job handles AI call failure gracefully
- **WHEN** the `CVAnalyzer` agent throws an exception (invalid response, timeout, API error)
- **THEN** the job catches the exception
- **AND** sets `statut_analyse = 'failed'`
- **AND** stores the error message in `message_erreur`

#### Scenario: Job handles missing analyse gracefully
- **WHEN** the `AnalyzeCandidateJob` is dispatched with a non-existent `analyse` ID
- **THEN** the job fails gracefully without throwing an unhandled exception

#### Scenario: Job handles missing offer or candidate gracefully
- **WHEN** the `AnalyzeCandidateJob` is dispatched but the associated `candidat` or `offre` is missing
- **THEN** the job logs a warning and returns without throwing an exception

#### Scenario: Job failed() lifecycle hook catches queue failures
- **WHEN** the job fails at the queue level (e.g., max attempts exceeded)
- **THEN** the `failed()` method updates `statut_analyse = 'failed'` and stores the exception message

### Requirement: Analysis status is tracked end-to-end
The system SHALL track the analysis lifecycle: `pending` → `processing` → `completed` or `failed`.

#### Scenario: Initial status is pending
- **WHEN** a candidate is first submitted
- **THEN** the `analyses` row has `statut_analyse = 'pending'`

#### Scenario: Failed status is available for error cases
- **WHEN** the analysis job encounters an error
- **THEN** `statut_analyse` is set to `'failed'` and `message_erreur` stores the error message
