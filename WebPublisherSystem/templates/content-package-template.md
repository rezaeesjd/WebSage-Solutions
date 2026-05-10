# Content Package Template (Runbook)

## Preflight
1. Command classified
2. Hard clarify gate outcome recorded (`PASS|BLOCKED_MISSING_INPUT|PROVISIONAL_WITH_APPROVAL`)
3. If provisional, approval evidence captured

## Build Order (No Skips)
1. `source-facts.md` (with provenance matrix + status enums)
2. `brief.md`
3. `keywords.md`
4. `blog-post.md` (public-only)
5. `faq.md`
6. `meta.json`
7. `internal-links.md`
8. `automation-notes.md`
9. `qa-report.md`

## Pre-Completion Validation
- Required files complete
- Public-content cleanliness checks pass
- Link provenance checks pass
- Metadata/status schema checks pass
- Conversion blocker checks pass

## Completion Output
- Final generation status
- Blockers summary
- Human review actions
