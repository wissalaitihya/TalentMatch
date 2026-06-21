## Context

The project has all features implemented and tested (96 passing tests). The codebase follows Laravel conventions, uses Pest for testing, OpenSpec for change management, and the laravel/ai SDK for AI features. The final deliverable requires documentation so the trainer can evaluate the project without guessing its architecture or setup.

Key constraints:
- No secrets or real API keys may be exposed
- All documentation must be in French (project is French-targeted) or English depending on the existing convention — README should be in English (standard open-source practice) while demo scenario can reference French UI labels
- Documentation must reflect the actual project state (features, schema, tools, tests)
- AI explanations must mention that calls are faked in tests and require real API keys in production

## Goals / Non-Goals

**Goals:**
- Clear, professional `README.md` that covers all requested sections
- Step-by-step demo scenario for evaluator walkthrough
- AI architecture documentation explaining the two-layer design
- Accurate representation of the current project state

**Non-Goals:**
- No new features, routes, controllers, models, migrations, or views
- No UI changes
- No deployment or CI configuration
- No API key generation or provider setup documentation beyond `.env.example`

## Decisions

1. **README in English** — standard for open-source Laravel projects on GitHub, even when the UI is in French
2. **Demo scenario in French** — the UI labels are in French, so the walkthrough should match what the evaluator sees
3. **AI workflow as separate doc** — the two-layer architecture (structured output agent + conversational agent with tools) deserves its own dedicated explanation
4. **No autogeneration** — all docs written manually to ensure accuracy and quality
5. **Sections follow Laravel README conventions** — inspired by typical Laravel package READMEs (Installation, Usage, Testing, etc.)

## Risks / Trade-offs

- [Risk] README may become stale if features are added later → Mitigation: README is a living document that should be updated with each change
- [Trade-off] Detailed docs take time to write but are essential for evaluation — no shortcut here
