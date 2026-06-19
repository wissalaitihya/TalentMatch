## 1. Database / Migration Setup

- [x] 1.1 Publish laravel/ai SDK migrations: `php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"` to create ai_conversations and ai_messages tables
- [x] 1.2 Create `drop_messages_table` migration to remove the custom messages table (no longer needed, replaced by SDK tables)
- [x] 1.3 Add `HasConversations` trait to User model for conversation relationship
- [x] 1.4 Run all migrations and verify schema — agent_conversations and agent_conversation_messages created; messages table dropped

## 2. Tool Classes

- [x] 2.1 Run `php artisan make:tool GetCandidateAnalysis` — implemented with ownership check via `offre->user_id`
- [x] 2.2 Run `php artisan make:tool GetJobRequirements` — implemented with ownership check via `offre.user_id`
- [x] 2.3 Run `php artisan make:tool CompareCandidates` — implemented with ownership checks for both candidates
- [x] 2.4 Verify each tool returns formatted, human-readable strings (not raw JSON)
- [x] 2.5 Verify ownership: tools return clear "not found" message when user does not own the related offer

## 3. Assistant Agent

- [x] 3.1 Run `php artisan make:agent AssistantAgent` in `app/Ai/Agents/` implementing Agent, Conversational, HasTools
- [x] 3.2 Add `Promptable` and `RemembersConversations` traits
- [x] 3.3 Implement instructions() with dynamic context injection (candidate name, offer title, analysis fields)
- [x] 3.4 Implement tools() returning the three tool instances
- [x] 3.5 Add PHP attributes for provider, model, max tokens, temperature, timeout

## 4. Orchestrator Service

- [x] 4.1 Create `app/Services/AssistantOrchestrator.php` service class
- [x] 4.2 Implement `ask(Analyse $analyse, User $user, string $message): array` method
- [x] 4.3 Method creates/continues conversation via `forUser()` / `continue()` with analysis context
- [x] 4.4 Method returns the assistant response text and conversation ID

## 5. Controller + Route + Validation

- [x] 5.1 Create `app/Http/Requests/AssistantChatRequest.php` with rule: message required string max 2000
- [x] 5.2 Create `AnalyseController` with `show` and `chat` methods — inject orchestrator, authorize via FormRequest ownership check, return JSON response
- [x] 5.3 Register route: POST `/analyses/{analyse}/chat` (named `analyses.chat`) within auth middleware
- [x] 5.4 Add route for GET `/analyses/{analyse}` (named `analyses.show`) to display the chat interface

## 6. Blade Views / UI

- [x] 6.1 Create `resources/views/analyses/show.blade.php` — candidate analysis detail page with score, recommendation, strengths, gaps, missing skills, justification
- [x] 6.2 Create `resources/views/analyses/partials/chat.blade.php` — chat component with message display area and input form
- [x] 6.3 Add Alpine.js for chat interactivity: fetch POST on submit, loading state, error handling, auto-scroll
- [x] 6.4 Wire analyse show route in navigation or add link from offer show candidate list

## 7. Integration — Link from Offer Show Page

- [x] 7.1 Add "Détail" link on candidate name in offer show page pointing to `analyses.show`
- [x] 7.2 Ensure eager loading in OffreController show method (already loads analyses with candidat)

## 8. Pest Tests

- [x] 8.1 Create `tests/Feature/AssistantChatTest.php`:
  - [x] 8.1.1 Test guest redirect for chat access
  - [x] 8.1.2 Test authenticated user can view own analysis and ask question
  - [x] 8.1.3 Test user cannot access another user's analysis (404)
  - [x] 8.1.4 Test getCandidateAnalysis tool returns real saved data
  - [x] 8.1.5 Test getJobRequirements tool returns real offer data
  - [x] 8.1.6 Test compareCandidates compares two saved analyses
  - [x] 8.1.7 Test tools handle non-existent data gracefully
  - [x] 8.1.8 Test ownership enforcement via tools returns clear messages
  - [x] 8.1.9 Test tools return readable text not raw JSON

## 9. Final Verification

- [x] 9.1 Run `php artisan route:list` — both analyses.show and analyses.chat routes registered
- [x] 9.2 Run `vendor/bin/pint --format agent` — style fixes applied
- [x] 9.3 Run `php artisan test --compact` — 65/65 pass
