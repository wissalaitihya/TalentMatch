## ADDED Requirements

### Requirement: CVAnalyzer agent extracts structured data from CV text
The system SHALL have a `CVAnalyzer` agent implementing `HasStructuredOutput` that returns a strict JSON schema when given a CV text and job offer details.

#### Scenario: Agent returns complete structured analysis
- **WHEN** the `CVAnalyzer` agent receives a CV text, job title, job description, and required skills list
- **THEN** it returns a structured JSON with: `competences_extraites` (string[]), `annees_experience` (int 0-60), `niveau_etudes` (string), `langues` (string[]), `matching_score` (int 0-100), `points_forts` (string[]), `lacunes` (string[]), `competences_manquantes` (string[]), `recommandation` (convoquer|attente|rejeter), and `justification` (string)

#### Scenario: Agent uses low temperature for deterministic output
- **WHEN** the `CVAnalyzer` is invoked
- **THEN** it SHALL use `#[Temperature(0.1)]` for low-variance, deterministic responses

#### Scenario: Agent uses cheapest available model
- **WHEN** the `CVAnalyzer` is invoked
- **THEN** it SHALL use `#[UseCheapestModel]` to minimize API costs

### Requirement: CVAnalyzer follows strict no-invention rules
The agent SHALL NOT invent skills, experience, languages, or education. It SHALL base all extracted data solely on the provided CV text.

#### Scenario: Agent does not invent missing information
- **WHEN** the CV text does not mention a specific skill, language, or education level
- **THEN** the agent SHALL NOT include it in the output

#### Scenario: Agent mentions uncertainty in justification
- **WHEN** the CV text is unclear or ambiguous about experience, skills, or education
- **THEN** the agent SHALL note the uncertainty in the `justification` field

### Requirement: CVAnalyzer can be faked in tests
The system SHALL support `CVAnalyzer::fake()` to return predefined structured data without making real AI API calls.

#### Scenario: Fake returns predefined structured data
- **WHEN** `CVAnalyzer::fake()` is called with a callback returning a structured array
- **THEN** subsequent agent invocations return the predefined data without calling the AI provider

#### Scenario: Fake callback can inspect the prompt
- **WHEN** `CVAnalyzer::fake()` is called with a function callback accepting a prompt string
- **THEN** the callback can inspect and assert on the prompt content
