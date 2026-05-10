# COMMANDS.md

## Command Router

Route by explicit prefix first:
1. `WPS:GENERATE_CONTENT`
2. `WPS:PUBLISH_BLOG`
3. `WPS:GENERATE_AND_PUBLISH`
4. `WPS:IMPLEMENT_GENERATION_PROCESS_IMPROVEMENTS`
5. `WPS:GENERATION_PROCESS_QA`
6. `WPS:CLARIFY`

If no prefix:
- Tour/package creation intent → treat as `WPS:GENERATE_CONTENT`.
- Strategy/process/template/QA intent → system-improvement mode only (no tour package writes).

## Hard Clarify Gate (Mandatory)

Before writing any package file, run a preflight gate.

### Required intake fields
- `canonical_tour_title`
- `active_brand` (default only if not overridden)
- `source_payload` (raw facts / itinerary / inclusions / exclusions / policy)
- `website_link_policy` (`final_url_provided` or `placeholder_approved`)

### Gate outcomes
- **PASS**: continue generation.
- **BLOCK**: emit `STATUS:BLOCKED_MISSING_INPUT` and route to `WPS:CLARIFY` with explicit missing fields.
- **PROVISIONAL**: allowed only with explicit user approval captured in metadata.

## Clarification Recording Rules

If blocked/provisional, store in `meta.json` and `qa-report.md`:
- `clarifications_needed` (array)
- `blocking_issues` (array)
- `conversion_blockers` (array)

## WebsiteLink Blocker Rules

- `website_link` is required for conversion-complete state.
- If missing, use `{{WebsiteLink}}` only when user approves provisional generation.
- While `{{WebsiteLink}}` exists in CTA/public metadata:
  - `publish_status` cannot be `published`
  - `qa_status` must not be `pass`
  - add `conversion_blockers` entry: `missing_website_booking_url`

## Link Provenance Rules

Every external public link in `blog-post.md`, `faq.md`, and `meta.json` must:
- appear in `source-facts.md` URL matrix
- include a source pointer (input field or snippet)

Unknown links must remain placeholders and be flagged in QA.

## Product Code Separation Rules

- Keep `product_reference_code` in metadata artifacts only.
- If multiple codes exist, separate as:
  - `internal_product_code`
  - `supplier_product_code`
  - `ota_product_code`
- Never expose any product code in public content files (`blog-post.md`, `faq.md`).

## Public Blog Cleanliness Rules

`blog-post.md` must not include admin/internal headings:
- Page Title
- URL Slug
- Meta Description
- Primary Keyword
- Funnel Stage
- Internal Linking Suggestions
- Schema/Debug/QA notes

## Completion Contract (Generation)

A generation run is complete only if all are true:
- all required package files exist
- `source-facts.md` exists with provenance matrix + field statuses
- `qa-report.md` exists with severity-ranked findings
- `meta.json` validates against schema guidance
- final status is honest and non-published unless live verification was performed
