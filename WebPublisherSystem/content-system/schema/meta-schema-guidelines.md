# meta-schema-guidelines.md

## Required fields
- brand
- canonical_tour_title
- internal_product_code
- supplier_product_code
- ota_product_code
- page_title
- slug
- public_slug
- meta_description
- primary_keyword
- funnel_stage
- cta_primary
- website_link
- tripadvisor_link
- viator_link
- publish_status
- human_review_required
- qa_status
- last_qa_date
- workflow_phase
- workflow_status
- clarifications_needed
- blocking_issues
- conversion_blockers
- blockers
- last_phase_update_utc

## Status enums
- `publish_status`: `draft|ready_for_review|needs_fix|ready_for_sync|needs_live_verification|published`
- `workflow_phase`: `intake|clarify|facts_locked|draft_generated|qa_complete|review_ready|published`
- `workflow_status`: `in_progress|blocked|complete`
- `qa_status`: `pending|pass|fail|fail_links|fail_schema|fail_provenance`

## Validation rules
- `canonical_tour_title` must be present and non-empty.
- `slug` and `public_slug` must be kebab-case.
- if `workflow_status = blocked`, `blocking_issues` and/or `blockers` must be non-empty.
- if `website_link = {{WebsiteLink}}`, then:
  - `publish_status != published`
  - `qa_status != pass`
  - `conversion_blockers` includes `missing_website_booking_url`
- `clarifications_needed`, `blocking_issues`, `conversion_blockers`, and `blockers` must be arrays.

## Cross-file consistency checks
- All public external links must appear in `source-facts.md` URL registry.
- Codes shown in metadata must not appear in `blog-post.md` or `faq.md`.
