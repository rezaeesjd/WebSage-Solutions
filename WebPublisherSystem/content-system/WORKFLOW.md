# WORKFLOW.md

## 0 → Content Package Workflow (Strict)

## Phase 0 — Intake & Command Classification
- Detect command prefix.
- If no prefix, classify user intent.
- Refuse unintended publishing actions during generation tasks.

## Phase 1 — Preflight Hard Clarify Gate
Build a preflight checklist and decide:
- `PASS`
- `BLOCKED_MISSING_INPUT`
- `PROVISIONAL_WITH_APPROVAL`

Critical blockers (must stop unless provisional approval is explicit):
- missing canonical tour title
- missing source payload
- unclear brand identity
- missing website booking URL policy

If blocked, output `WPS:CLARIFY` questions and stop copy generation.

## Phase 2 — Source Facts Lock
Create `source-facts.md` first.

### Required provenance matrix columns
- Field
- Value
- Status (`confirmed|missing|conflicted|inferred|needs_human_review|not_applicable`)
- Source reference (raw input line/field)
- Confidence (`high|medium|low`)
- Public-safe (`yes|no`)

No public copy can be drafted before this phase is complete.

## Phase 3 — Missing/Conflict Resolution
For each critical field (URLs, policy, logistics, pricing/reviews when used):
- keep confirmed value
- or mark missing/conflicted with clear handling rule
- avoid unverifiable claims in downstream files

## Phase 4 — Strategy + Conversion Blueprint
Define:
- intent stage map (TOFU/MOFU/BOFU)
- primary conversion action (website booking)
- trust support action (OTA references secondary)
- friction reducers required in copy (meeting point, duration, policy, accessibility)

## Phase 5 — Package Generation
Generate required files in order:
1. `source-facts.md`
2. `brief.md`
3. `keywords.md`
4. `blog-post.md`
5. `faq.md`
6. `meta.json`
7. `internal-links.md`
8. `automation-notes.md`
9. `qa-report.md`

## Phase 6 — Structural + Cleanliness Validation
Validate:
- required files exist
- `meta.json` required keys + enums are valid
- public content excludes admin/internal labels
- product codes are not leaked into public files

## Phase 7 — Link Provenance + Blockers
Validate:
- website/TripAdvisor/Viator links traceable in source-facts
- placeholders only where allowed
- missing website link recorded as conversion blocker

## Phase 8 — QA Decision & Final Status
`qa-report.md` must include:
- category checks and outcomes
- blockers with severity (`high|medium|low`)
- owner/action recommendations
- final package status recommendation

Allowed generation-stage status outcomes:
- `draft`
- `ready_for_review`
- `needs_fix`

Publishing statuses remain a separate workflow concern.
