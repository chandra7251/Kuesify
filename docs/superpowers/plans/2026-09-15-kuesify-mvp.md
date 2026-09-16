# Kuesify MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:executing-plans` for inline task-by-task execution.

**Goal:** Deliver a Laravel 13, Vue, Inertia, Reverb quiz platform with tenant isolation, manual/AI quiz creation, live sessions, homework grading, gamification, analytics, and automated E2E coverage.

**Architecture:** Laravel modular monolith owns authorization, scoring, queues, and tenant isolation. Vue + Inertia owns mobile-first UI. Reverb broadcasts session events; Redis backs queues and leaderboard cache. All score-changing work happens on the server.

**Tech Stack:** PHP 8.3+, Laravel 13, Vue 3, TypeScript, Inertia 3, Tailwind, MySQL 8, Redis, Horizon, Reverb, Echo, Gemini API, Pest browser tests.

**Spec:** `PRD_v2.2_Merged_Draft.md`

## Global Constraints

- Use TDD: each behavior gets a failing test before production implementation.
- Use `organization_id` scope and Policies for tenant-bound data.
- Live sessions only contain auto-graded questions; essays are Self-Paced manual review.
- Do not add audio/video, billing, SSO, marketplace, or mobile-native apps.
- Validate files as PDF/PPT/PPTX, at most 25 MB, and apply a 50 page/slide limit during extraction.

---

### Task 1: Scaffold and Quality Tooling

- [ ] Create Laravel 13 Vue/Inertia project in the workspace.
- [ ] Configure MySQL, Redis, queues, Reverb, frontend tooling, test database, lint/type/build scripts.
- [ ] Verify `php artisan test`, `npm run build`, lint, and type-check.

### Task 2: Identity and Tenant Foundation

- [ ] Create organizations, memberships, roles, tenant middleware, Global Scope, Policies, factories, and seeders.
- [ ] Write tenant-isolation and authorization feature tests before code.
- [ ] Verify account, organization, and forbidden cross-tenant flows.

### Task 3: Taxonomy and Question Bank

- [ ] Create categories, tenant tags, question bank, import/export, validation, and search/filter UI.
- [ ] Test cross-tenant tags, invalid import rollback, and question reuse.

### Task 4: Quiz Builder and Publication

- [ ] Create quiz metadata, four question types, image media, ordering, preview, publication rules, and mobile UI.
- [ ] Test creator ownership, validation, ordering, and live-session essay restriction.

### Task 5: AI Generation Pipeline

- [ ] Create upload records, document extraction, Gemini abstraction, schema validation, queue jobs, retry states, quota guard, and review UI.
- [ ] Test upload validation, invalid AI JSON, retry/failure, and review-before-publish.

### Task 6: Live Quiz and Realtime Scoring

- [ ] Create sessions, PIN/QR, guest identity token, lobby controls, broadcasts, authoritative timer, atomic response/scoring, leaderboard cache, and podium UI.
- [ ] Test duplicate responses, late answers, reconnect, host controls, and multi-browser realtime E2E.

### Task 7: Self-Paced, Essay Review, and Gradebook

- [ ] Create attempts, deadlines, max attempts, objective scoring, pending essay review, creator grading, and result UI.
- [ ] Test deadline, max-attempt, pending-review, final-score, and gradebook flows.

### Task 8: Gamification, Reporting, and Admin

- [ ] Create XP, levels, badges, streaks, reports, exports, moderation, tenant dashboard, and platform monitoring.
- [ ] Test idempotent awards, exports, tenant scope, and moderation authorization.

### Task 9: Full Verification

- [ ] Run backend/unit/feature/browser E2E suites.
- [ ] Run frontend lint, type-check, and production build.
- [ ] Run realtime multi-browser test against Reverb and a 100-participant load smoke test.
- [ ] Fix all regressions before completion.
