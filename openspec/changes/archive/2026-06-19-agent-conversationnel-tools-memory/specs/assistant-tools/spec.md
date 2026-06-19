## ADDED Requirements

### Requirement: getCandidateAnalysis tool returns saved analysis data

The system SHALL provide a `GetCandidateAnalysis` tool that retrieves the full structured analysis for a given candidate. The tool SHALL return real saved data from the `analyses` table, including candidate name, offer title, matching score, recommendation, strengths, gaps, missing skills, extracted skills, languages, education level, years of experience, and justification.

#### Scenario: Tool returns analysis for own candidate
- **WHEN** the tool is called with a valid candidate ID that belongs to an offer owned by the authenticated user
- **THEN** the tool returns the candidate name, offer title, all analysis fields (score, recommendation, strengths, gaps, missing skills, extracted skills, languages, education, experience, justification), and the analysis status

#### Scenario: Tool returns not found for another user's candidate
- **WHEN** the tool is called with a candidate ID linked to an offer owned by another user
- **THEN** the tool returns a message indicating that the requested candidate data cannot be retrieved

#### Scenario: Tool handles missing analysis gracefully
- **WHEN** the tool is called with a valid candidate ID but the analysis has null/missing fields
- **THEN** the tool returns the available data and clearly indicates which fields are unavailable

#### Scenario: Tool handles non-existent candidate
- **WHEN** the tool is called with a candidate ID that does not exist
- **THEN** the tool returns a message indicating the candidate was not found

### Requirement: getJobRequirements tool returns offer details

The system SHALL provide a `GetJobRequirements` tool that retrieves the job offer details, including title, description, required skills, and minimum experience level.

#### Scenario: Tool returns offer for own offer
- **WHEN** the tool is called with a valid offer ID owned by the authenticated user
- **THEN** the tool returns the offer title, description, required skills array, and minimum experience level

#### Scenario: Tool returns not found for another user's offer
- **WHEN** the tool is called with an offer ID owned by another user
- **THEN** the tool returns a message indicating the offer data cannot be retrieved

#### Scenario: Tool handles non-existent offer
- **WHEN** the tool is called with an offer ID that does not exist
- **THEN** the tool returns a message indicating the offer was not found

### Requirement: compareCandidates tool compares two saved analyses

The system SHALL provide a `CompareCandidates` tool that compares two analyzed candidates using their saved analyses. The tool SHALL NOT re-run CV analysis or call the AI analysis job.

#### Scenario: Tool compares two candidates from own offers
- **WHEN** the tool is called with two valid candidate IDs, both belonging to offers owned by the authenticated user
- **THEN** the tool returns both analyses side by side with a comparison summary including differences in score, recommendation, strengths, gaps, and missing skills

#### Scenario: Tool refuses comparison with another user's candidate
- **WHEN** the tool is called with a candidate ID linked to another user's offer
- **THEN** the tool returns a clear message that the comparison cannot be completed

#### Scenario: Tool handles incomparable candidates
- **WHEN** the tool is called with two candidate IDs but the analyses have different structures or cannot be meaningfully compared
- **THEN** the tool returns a clear safe message that the candidates are not comparable
