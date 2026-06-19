## Context

Candidate submission and structured AI analysis are implemented. The analysis data sits in the `analyses` table with scores, recommendations, strengths, gaps, and justification. RH agents currently see this data as a static table row on the offer show page. There is no way to ask follow-up questions, drill into reasoning, or compare candidates conversationally.

The laravel/ai SDK (v0.8.1) is already installed. It provides `HasTools` for tool calling, `RemembersConversations` for automatic conversation persistence, and `HasConversations` for user-scoped conversation retrieval. These capabilities are unused so far.

A custom `messages` table exists with `analyse_id` FK — it was scaffolded early but predates the SDK's built-in memory system. It will be replaced by the SDK tables.

## Goals / Non-Goals

**Goals:**
- Three real Laravel AI tool classes: `GetCandidateAnalysis`, `GetJobRequirements`, `CompareCandidates` in `app/Ai/Tools/`
- `AssistantAgent` in `app/Ai/Agents/` implementing `Agent`, `Conversational`, and `HasTools` interfaces
- Conversation memory via SDK's `RemembersConversations` trait (ai_conversations, ai_messages tables)
- Publish and run SDK migrations; drop custom `messages` table
- Thin `AssistantChatController` with a single `__invoke` or `store` method
- Blade chat component on the candidate analysis detail page
- Ownership enforcement: every tool verifies that the fetched data belongs to the authenticated user's offers
- Full Pest test coverage with faked agents and tools

**Non-Goals:**
- Re-running CV analysis job (AnalyzeCandidateJob stays as-is)
- Editing analysis results manually via chat
- Comparing candidates across offers owned by different users
- Full candidate comparison dashboard (UI comparison is chat-only)
- Real AI provider configuration or deployment setup

## Decisions

**1. Use SDK's RemembersConversations trait instead of custom messages table**
- *Rationale*: The SDK provides a well-tested, maintained conversation persistence layer. Using it avoids reinventing the wheel and ensures compatibility with future SDK updates. The `forUser()` scope maps directly to our ownership model.
- *Alternatives considered*: Implementing the `Conversational` interface manually with the existing `messages` table. Rejected because the SDK trait handles all CRUD automatically and is the documented approach.
- *Table migration*: The custom `messages` table will be dropped via a new migration. A fresh `drop_messages_table` migration will run after the SDK migration is published.

**2. Agent receives analysis context via system prompt, not a DB link table**
- *Rationale*: The assistant always operates in the context of a specific candidate analysis. Rather than adding a pivot column to the SDK's `ai_conversations` table, we inject the context (candidate name, offer title, analysis fields) directly into the agent's instructions on each prompt. This keeps the SDK tables untouched and avoids MCD changes.
- *How it works*: When the controller calls `->forUser($user)->prompt(...)`, it also passes dynamic instructions that include the current analysis context. The agent knows "you are answering about candidate X on offer Y" from the instructions, not from a database column.

**3. Tools verify ownership at handle() time, not in middleware**
- *Rationale*: Each tool receives the authenticated user via constructor injection. The `handle()` method checks `$offre->user_id === $this->user->id` before returning data. This fails closed — if the user does not own the offer, the tool returns a clear "not found" message instead of raw data.
- *Consistency*: Matches the existing OffreController pattern of checking ownership before access.

**4. Thin controller with inline agent orchestration**
- *Rationale*: The assistant flow is simple: receive message → create/continue conversation → prompt agent → return response. A dedicated service class (`AssistantOrchestrator`) wraps this but the controller stays thin (validate → call service → return).
- *Structure*: `AssistantChatController` injects the orchestrator. The orchestrator handles agent creation, tool registration, conversation scoping, and response formatting.

**5. Chat is embedded as a Blade partial on the candidate analysis detail page**
- *Rationale*: Keeping the chat inline on the existing page (rather than a separate page) provides immediate context — the agent sees the analysis they're asking about. The chat renders via a Blade partial with Alpine.js for message submission and display.
- *Why Alpine*: The existing Laravel Breeze stack uses Alpine.js. No new frontend dependency is needed. A lightweight component handles fetch + loading states + error display.

## Risks / Trade-offs

| Risk | Mitigation |
|------|------------|
| SDK's ai_messages table conflicts with existing custom messages table | Drop custom messages table in a migration before publishing SDK tables |
| Agent hallucinates data when tools fail | Agent instructions explicitly forbid guessing from model memory. Tools return clear "data not found" strings |
| Conversation grows too long (context window limits) | The `RemembersConversations` trait automatically manages conversation history. We can configure `MaxTokens` on the agent to fit within model limits |
| Ownership check error leaks candidate existence | Tools return generic "unable to retrieve the requested information" if ownership check fails, not "candidate exists but is not yours" |
| User submits empty message | Validate via Form Request: message required, string, min 1, max 2000 |
