# WORKFLOW.md

## 0 → Content Package Generation Workflow (Strict)

### Phase 0: Intake + Clarify Gate
- classify command.
- validate required inputs.
- if missing critical inputs, set blocked status and stop writing tour copy.

### Phase 1: Source-Facts Provenance Matrix
Create `source-facts.md` first, including a matrix:
- Fact
- Value
- Source snippet/input field
- Confidence (`high|medium|low`)
- Public-safe (`yes|no`)
- Needs human verification (`yes|no`)

No marketing copy is allowed before this matrix exists.

### Phase 2: Missing Information Handling
For each required field, assign one:
- `provided`
- `missing_placeholder_used`
- `missing_blocking`

Rules:
- Missing factual UX enhancers (e.g., language/accessibility) can proceed with omission.
- Missing legal/commercial critical data (website booking URL for CTA, cancellation policy if referenced) must be flagged as blocker or omitted from claims.

### Phase 3: Strategy + Conversion Mapping
Require explicit conversion plan:
- TOFU/MOFU/BOFU mapping
- primary conversion event (website click/book)
- secondary trust event (OTA proof only)
- friction removers (meeting point clarity, duration clarity, cancellation clarity)

### Phase 4: Content Package Assembly
Generate all required files and enforce clean public body:
- no admin labels in `blog-post.md`
- no raw placeholders except approved link placeholders
- no product codes in public content

### Phase 5: Link Provenance Validation
Validate links against source-facts:
- Every non-internal public link must exist in source-facts URL section.
- If a URL is absent from source-facts, fail QA link provenance.

### Phase 6: QA + Release Decision
Produce `qa-report.md` with final recommendation:
- `ready_for_review`
- `needs_fix`
- `ready_for_sync`
- `needs_live_verification`
- `published` (live checks required)
