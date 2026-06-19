## MODIFIED Requirements

### Requirement: compareCandidates tool compares two saved analyses

The system SHALL provide a `CompareCandidates` tool that compares two analyzed candidates using their saved analyses. The tool SHALL NOT re-run CV analysis or call the AI analysis job. The tool SHALL only compare candidates that belong to the same offer, have completed analyses, and are owned by the authenticated user.

#### Scenario: Tool compares two candidates from same own offer with completed analyses
- **WHEN** the tool is called with two valid candidate IDs, both belonging to the same offer owned by the authenticated user, and both analyses are completed
- **THEN** the tool returns both analyses side by side with a comparison summary including differences in score, recommendation, strengths, gaps, and missing skills

#### Scenario: Tool refuses comparison with another user's candidate
- **WHEN** the tool is called with a candidate ID linked to another user's offer
- **THEN** the tool returns a clear message that the comparison cannot be completed

#### Scenario: Tool refuses comparison of candidates from different offers
- **WHEN** the tool is called with two candidate IDs that belong to different offers
- **THEN** the tool returns a clear message that candidates must belong to the same offer

#### Scenario: Tool refuses comparison when one analysis is not completed
- **WHEN** the tool is called with two candidate IDs but one or both analyses are not in "completed" status
- **THEN** the tool returns a clear message that both analyses must be completed first

#### Scenario: Tool handles non-existent candidate
- **WHEN** the tool is called with a candidate ID that does not exist
- **THEN** the tool returns a message indicating the candidate was not found

#### Scenario: Tool handles missing analysis gracefully
- **WHEN** the tool is called with valid candidate IDs but one analysis has null/missing fields
- **THEN** the tool returns the comparison with available data and indicates which fields are unavailable
