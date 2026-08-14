<!--
Sync Impact Report
- Version change: TEMPLATE → 1.0.0 (initial ratification)
- Modified principles: n/a (first draft, no prior versioned principles)
- Added sections:
  - Core Principles: I. Multi-Client Reusability, II. White-Label by Default,
    III. Settings-Driven Theming (No Page Builder), IV. Module Test Coverage,
    V. Simplicity & Dependency Discipline
  - Deployment & Client Setup Standards
  - Development Workflow
  - Governance
- Removed sections: none (template placeholders replaced)
- Templates requiring updates:
  - .specify/templates/plan-template.md: ⚠ pending manual review (Constitution Check section should reference these 5 principles explicitly)
  - .specify/templates/spec-template.md: ⚠ pending manual review (ensure per-client toggle/config requirements captured)
  - .specify/templates/tasks-template.md: ⚠ pending manual review (ensure testing + docs task categories reflect Principles IV & Deployment Standards)
  - CLAUDE.md: ✅ no outdated agent-specific references found
  - README.md: ✅ consistent with principles (no changes needed)
- Follow-up TODOs: none
-->

# Simetri Constitution

## Core Principles

### I. Multi-Client Reusability

Every feature MUST be built as a config-driven or toggleable module, never hardcoded
for a single client. Client-specific data (content, branding, feature flags) MUST live
in settings, seeders, or `.env`-driven configuration — never hardcoded in views,
migrations, or business logic. A module that cannot be disabled or re-themed without
code changes is not release-ready.

**Rationale**: Simetri exists to be cloned/deployed across many client projects. Any
client-specific assumption baked into code creates drift that must be manually
resolved on every new deployment, defeating the purpose of a starter kit.

### II. White-Label by Default

The admin panel (name, logo, favicon, brand color) and any starter-kit-identifying
branding MUST be fully replaceable via settings before a client handoff. No
"Simetri", starter-kit template names, or placeholder branding may ship visible to
end clients or their site visitors in a release build.

**Rationale**: Clients are paying for their own branded product, not a visibly
repurposed template. Leaking starter-kit identity undermines the product's
credibility and violates typical client contracts.

### III. Settings-Driven Theming, No Page Builder (NON-NEGOTIABLE)

Theming MUST be implemented through Spatie Settings + CSS variables and a fixed set
of pre-built Blade section variants (e.g. hero-v1/v2, about-grid/timeline). Building
a drag-and-drop or freeform page builder is explicitly OUT OF SCOPE. New visual
flexibility MUST be delivered as a new named section variant, not a generic builder
primitive.

**Rationale**: A full page builder is a large, open-ended surface area that this
project has deliberately chosen not to take on (see Epic 4 scope). Constraining
customization to settings + variants keeps the codebase simple, predictable, and
fast to extend per client without reintroducing page-builder complexity later.

### IV. Module Test Coverage

Every content module (Services, Portfolio, Team, Testimonials, Client Logos, Career,
Blog, Contact Us, Custom Page, Banner, Menu Builder) MUST ship with at least a basic
feature test covering its primary CRUD/render path before it is considered done.
Modules without tests MUST NOT be marked complete in the tracker.

**Rationale**: With many client deployments reusing the same modules, a regression
in one module silently breaks every client site built on top of it. Baseline feature
tests are the minimum guardrail against that blast radius.

### V. Simplicity & Dependency Discipline

Prefer well-maintained, widely-used packages (Spatie ecosystem, official Filament
plugins) over custom-built infrastructure. Before adding a new dependency, confirm
it is actively maintained and its license is compatible with commercial client
resale. All third-party dependencies and their licenses MUST be audited before a
v1.0 release (see Epic 7). Avoid speculative abstractions — build for the modules
and clients that exist today, not hypothetical future requirements.

**Rationale**: This is a commercial starter kit resold/redeployed to clients;
license incompatibility or unmaintained dependencies become legal and support
liabilities at scale, not just technical debt.

## Deployment & Client Setup Standards

- New client setup MUST be reproducible via the `app:setup-client` artisan command
  (generate `.env`, set app name, generate key, clear cache) rather than manual steps.
- Demo/showcase content MUST be seedable and MUST NOT be required in production
  client deployments (seeders are for sales demos only, never auto-run in
  production migrations).
- Deployment documentation (server requirements, deploy steps, `.env` setup,
  go-live checklist) MUST be kept up to date as modules are added; undocumented
  modules are considered incomplete.
- Versioning/update strategy across client forks MUST be explicit (template repo
  with upstream remote, or private composer package) and documented before any
  client repo is cut from Simetri.

## Development Workflow

- Work is tracked as Epics → Tasks in Jira (project `SIM`); spec-kit features
  SHOULD reference the corresponding Jira epic/task key where one exists.
- Each module PR/feature MUST satisfy Principle IV (test coverage) before merge.
- Branding, theming, or panel-identity changes MUST be checked against Principle
  II (White-Label by Default) during review.
- Any proposal to add freeform layout/page-builder capability MUST be rejected or
  escalated as a constitution amendment (Principle III is NON-NEGOTIABLE), not
  merged as a regular feature.

## Governance

This constitution supersedes ad-hoc technical decisions and prior undocumented
practice. Amendments require: (1) a documented rationale, (2) a version bump per
the policy below, and (3) propagation review across `.specify/templates/*` and
`CLAUDE.md` in the same change.

**Versioning policy**:

- MAJOR: Backward-incompatible principle removal/redefinition (e.g. dropping the
  no-page-builder constraint).
- MINOR: New principle or materially expanded section added.
- PATCH: Wording clarifications, typo fixes, non-semantic refinements.

All feature plans and PRs MUST verify compliance with these principles during
review; unjustified complexity (violations of Principle III or V) must be called
out explicitly and either justified in the plan's Complexity Tracking section or
removed. Use `CLAUDE.md` for agent-facing runtime development guidance.

**Version**: 1.0.0 | **Ratified**: 2026-07-31 | **Last Amended**: 2026-07-31
