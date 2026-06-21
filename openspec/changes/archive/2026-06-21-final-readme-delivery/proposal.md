## Why

TalentMatch is functionally complete with all features implemented (auth, offres CRUD, candidate submission, AI analysis, assistant chat, candidate comparison, and final quality tests), but lacks the final documentation that would allow a trainer, evaluator, or new developer to understand, install, run, test, and evaluate the project. Without a clear README and delivery notes, the project's value and architecture are opaque, and the submission is incomplete.

## What Changes

- Create a comprehensive `README.md` at project root covering all required sections: project description, problem context, features, tech stack, database model, AI features, assistant tools, queue worker, installation, environment variables, migration/seed, running the queue, tests, demo scenario, OpenSpec workflow, security notes, limitations, and project status
- Create `docs/demo-scenario.md` with a step-by-step walkthrough of the full user journey from registration to candidate analysis and assistant chat
- Create `docs/ai-workflow.md` explaining the two-layer AI architecture (structured CV analysis agent + conversational assistant with tools), how they interact, and how they are tested with fakes
- Optionally update `docs/database/README.md` to reflect the current schema
- No business logic changes, no new features, no UI changes, no migration changes

## Capabilities

### New Capabilities

- **Documentation**: Three new documentation files at project root and in `docs/` that explain the entire project to an external reader

### Modified Capabilities

None. All existing capabilities remain unchanged.

## Impact

- `README.md` — new file at project root
- `docs/demo-scenario.md` — new file
- `docs/ai-workflow.md` — new file
- `docs/database/README.md` — may be updated if needed
- No new code, routes, migrations, controllers, models, or views
- No changes to `.env`, `config/`, or dependencies
