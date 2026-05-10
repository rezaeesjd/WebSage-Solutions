# COMMANDS.md

## Command Router (Hard Clarify Gate)

If the user prompt is ambiguous, stop and classify before any file writes.

1. Detect prefix:
   - `WPS:GENERATE_CONTENT`
   - `WPS:PUBLISH_BLOG`
   - `WPS:GENERATE_AND_PUBLISH`
   - `WPS:IMPLEMENT_GENERATION_PROCESS_IMPROVEMENTS`
2. If no prefix and prompt is tour-specific content creation, default to `WPS:GENERATE_CONTENT`.
3. If no prefix and prompt is process/system/templates/QA only, route to **SYSTEM IMPROVEMENT MODE** (no tour package writes).

## Hard Clarify Gate (Blocking)

Before generation, block and request missing essentials if these are unknown:
- canonical tour title
- active brand (default Milano Adventures only if not overridden)
- primary website booking URL policy (real URL provided vs placeholder accepted)
- source input payload (facts/itinerary/inclusions/policies)

If any blocker is missing, output `STATUS:BLOCKED_MISSING_INPUT` and list exact required inputs.

## WebsiteLink Blocker Rules

- `website_link` is mandatory for publish-ready states.
- If missing, generation may continue with `{{WebsiteLink}}`, but QA must force:
  - `publish_status: "needs_fix"` (or `ready_for_review` only with explicit human acceptance)
  - `qa_status: "fail_links"`
- `published` is impossible while `{{WebsiteLink}}` exists anywhere in public CTA fields.

## Product Code Separation Rules

- `product_reference_code` is operational metadata only.
- Never expose product code in `blog-post.md` or `faq.md`.
- Keep product code in:
  - `source-facts.md`
  - `meta.json`
  - optional internal ops notes

## Meta Phase/Status Markers

`meta.json` must include:
- `workflow_phase`: `intake|facts_locked|draft_generated|qa_complete|publish_ready|published`
- `workflow_status`: `in_progress|blocked|complete`
- `blockers`: string[]
- `last_phase_update_utc`: ISO-8601 UTC timestamp

## Required QA Artifact Rule

Every generation run must create/update `qa-report.md` with:
- pass/fail table
- blocker list
- provenance gaps
- publish recommendation
