## Why

RH agents need a conversational assistant to ask questions about analyzed candidates without re-reading full analysis pages. Currently, the analysis is static — the agent sees the score, strengths, and gaps but cannot ask follow-ups like "Why was this score given?" or "What interview questions should I ask based on this candidate's gaps?". A tool-based assistant with conversation memory fills this gap while ensuring answers are grounded in real saved data.

## What Changes

- New assistant chat UI on the candidate analysis detail page
- Three real Laravel AI tools (getCandidateAnalysis, getJobRequirements, compareCandidates) with ownership verification
- Conversation memory via laravel/ai SDK (ai_conversations, ai_messages tables)
- New routes and controller for chat message submission
- Pest test coverage for all assistant flows, tool calls, ownership enforcement, and memory persistence

## Capabilities

### New Capabilities
- `assistant-chat`: Chat interface and server-side orchestration for the conversational assistant. Includes the Blade chat UI, message submission endpoint, assistant invocation, and conversation memory scoped to the authenticated RH agent and candidate analysis context.
- `assistant-tools`: Three real Laravel AI tool implementations (getCandidateAnalysis, getJobRequirements, compareCandidates) that fetch saved database records with ownership verification. No free-form AI guesses — every fact comes from a tool call.

### Modified Capabilities
- <!-- No existing capabilities have requirement changes. The existing candidate analysis and CV submission flows remain unchanged. -->

## Impact

- **Database**: No new tables — uses existing laravel/ai SDK tables (ai_conversations, ai_messages) for conversation storage. No MCD/MLD changes.
- **Models**: No new models. May add a lightweight pivot or service class to scope conversations to candidate/analyse context, if SDK requires a local reference (minimal, justified in design).
- **Controllers**: New `AssistantChatController` (thin, delegates to a service class).
- **Services**: New `AssistantOrchestrator` service class that wires the LLM with tools, memory, and authorization.
- **Tools**: New tool classes under `app/Http/Tools/` implementing Laravel AI's tool interface.
- **Views**: New Blade chat component (inline or partial) on the candidate analysis page. Alpine.js for interactivity if needed.
- **Routes**: New authenticated route for POST message to assistant (scoped to analyse ID).
- **Dependencies**: Already available (laravel/ai SDK v0.8.1 with HasTools, RemembersConversations).
