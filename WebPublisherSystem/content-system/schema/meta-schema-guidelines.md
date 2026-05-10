# meta-schema-guidelines.md

## Required meta.json fields (generation)
- brand
- product_reference_code
- canonical_tour_title
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
- blockers
- last_phase_update_utc

## Validation constraints
- `publish_status` enum: `draft|ready_for_review|needs_fix|ready_for_sync|needs_live_verification|published`
- `workflow_phase` enum: `intake|facts_locked|draft_generated|qa_complete|publish_ready|published`
- `workflow_status` enum: `in_progress|blocked|complete`
- `website_link` must not be `{{WebsiteLink}}` when `publish_status = published`
- `blockers` must be non-empty when `workflow_status = blocked`
