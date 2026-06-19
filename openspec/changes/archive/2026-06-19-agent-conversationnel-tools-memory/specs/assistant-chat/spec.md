## ADDED Requirements

### Requirement: RH agent can ask questions about an analyzed candidate via chat

The system SHALL provide a chat interface on the candidate analysis detail page where an authenticated RH agent can type a question and receive answers from an AI assistant. The assistant SHALL use real Laravel tools to fetch saved analysis data and SHALL NOT invent scores, skills, languages, education, or experience.

#### Scenario: Authenticated user asks a question about their own candidate analysis
- **WHEN** an authenticated user submits a message on the assistant chat for a candidate analysis that belongs to one of their offers
- **THEN** the assistant receives the message, processes it using the available tools, and returns a text response

#### Scenario: Guest user is redirected to login
- **WHEN** an unauthenticated user tries to access the assistant chat
- **THEN** the system redirects to the login page

#### Scenario: User cannot chat about another user's analysis
- **WHEN** an authenticated user tries to access the assistant chat for a candidate analysis whose related offer is owned by another user
- **THEN** the system returns a 404 Not Found response

#### Scenario: Assistant refuses to answer when data is unavailable
- **WHEN** the user asks a question about data that does not exist (null/missing fields)
- **THEN** the assistant clearly states that the information is unavailable rather than inventing a value

#### Scenario: Assistant explains why a candidate received a score
- **WHEN** the user asks "Why did this candidate get this score?"
- **THEN** the assistant uses the saved analysis justification, strengths, gaps, and matching fields to explain the score

#### Scenario: Assistant suggests interview questions based on candidate gaps
- **WHEN** the user asks "What interview questions should I ask?"
- **THEN** the assistant suggests questions based on the saved `competences_manquantes` and `lacunes` fields

#### Scenario: Assistant explains missing skills
- **WHEN** the user asks about missing skills for the candidate
- **THEN** the assistant returns the list of `competences_manquantes` from the saved analysis

#### Scenario: Follow-up question preserves conversation context
- **WHEN** the user asks a follow-up question in the same conversation
- **THEN** the assistant references the previous messages and maintains context across messages

### Requirement: Chat UI displays messages with loading and error states

The system SHALL display the conversation history in a scrollable chat area on the candidate analysis detail page, with proper loading and error states.

#### Scenario: Chat displays existing messages on page load
- **WHEN** the user navigates to a candidate analysis detail page that has existing conversation messages
- **THEN** the chat area displays all previous messages with the user's question and the assistant's response

#### Scenario: Loading state is shown while waiting for assistant response
- **WHEN** the user submits a message and the assistant is processing
- **THEN** a loading indicator is displayed in the chat area

#### Scenario: Error state is shown if assistant call fails
- **WHEN** the assistant call fails due to a server error or timeout
- **THEN** an error message is displayed with an option to retry

### Requirement: Assistant respects ownership and security

The assistant SHALL only access data from offers owned by the authenticated RH agent.

#### Scenario: Assistant cannot access another user's candidate data
- **WHEN** the assistant tool is called with a candidate ID linked to an offer owned by another user
- **THEN** the tool returns a message indicating the data cannot be retrieved
