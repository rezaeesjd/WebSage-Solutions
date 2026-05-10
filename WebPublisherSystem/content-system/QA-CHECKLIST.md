# QA-CHECKLIST.md

## Gate 1 — Command & Clarify
- [ ] Correct command classification recorded
- [ ] Hard clarify preflight executed
- [ ] Missing critical input triggers block or explicit provisional approval
- [ ] `clarifications_needed` captured when applicable

## Gate 2 — Required File Set
- [ ] All required files exist (including `source-facts.md` and `qa-report.md`)
- [ ] Folder name is valid kebab-case
- [ ] No duplicate package folder for same canonical tour

## Gate 3 — Source Facts Provenance
- [ ] `source-facts.md` created before copy generation
- [ ] Provenance matrix present with required columns
- [ ] Each required field has status enum value
- [ ] Missing/conflicted fields are clearly listed

## Gate 4 — Missing Information Handling
- [ ] Missing website URL handled by blocker rules
- [ ] Missing policy/logistics fields not converted into invented claims
- [ ] Conflicted values flagged for human review

## Gate 5 — Link Provenance & CTA Priority
- [ ] Website link is primary CTA target
- [ ] TripAdvisor/Viator are secondary trust/support links only
- [ ] All external links trace to source-facts URL section
- [ ] Placeholders used only when data not provided and flagged in QA

## Gate 6 — Product Code Separation
- [ ] Codes stored in metadata/source-facts only
- [ ] Internal/supplier/OTA code distinctions are explicit when available
- [ ] No product codes appear in public content files

## Gate 7 — Public Content Cleanliness
- [ ] Exactly one traveler-facing H1
- [ ] No admin/internal labels in public article body
- [ ] No debug/schema/QA artifacts in public article body
- [ ] Internal linking suggestions are stored outside public body

## Gate 8 — Meta/Status Schema
- [ ] `meta.json` valid JSON
- [ ] Required keys exist
- [ ] Workflow phase/status keys valid
- [ ] Blocking/conversion fields present and accurate
- [ ] `publish_status` is honest and non-published unless verified in publish workflow

## Gate 9 — Conversion & Growth Readiness
- [ ] Early soft CTA present
- [ ] Strong final CTA present
- [ ] Booking-confidence details included (only from confirmed facts)
- [ ] Friction reducers included (logistics/policy/accessibility where available)

## Gate 10 — End-User Readiness
- [ ] Public copy is scannable and clear
- [ ] Claims are evidence-backed or omitted
- [ ] Next booking action is obvious
- [ ] Human review actions are explicit in QA report

## QA Report Minimum Output
- [ ] Pass/fail matrix by gate
- [ ] Issues grouped by type
- [ ] Severity assigned per issue
- [ ] Remediation actions with owner
- [ ] Final recommendation status
