# QA-CHECKLIST.md

## Mandatory QA Gates

### 1) Hard Clarify Gate
- [ ] Required inputs present
- [ ] Blockers declared when missing
- [ ] Workflow status reflects reality

### 2) Provenance Matrix Gate
- [ ] `source-facts.md` includes fact-by-fact provenance matrix
- [ ] No unsupported claims in public copy
- [ ] Conflicts explicitly listed

### 3) Missing Information Gate
- [ ] Missing fields categorized (`provided|missing_placeholder_used|missing_blocking`)
- [ ] Blocking missing fields stop publish-ready status

### 4) Link + WebsiteLink Gate
- [ ] Website link present in source facts + meta
- [ ] Website is primary CTA
- [ ] OTA links secondary only
- [ ] Placeholder links force non-published status

### 5) Product Code Separation Gate
- [ ] Product code present in `meta.json` if provided
- [ ] Product code absent from public blog/FAQ

### 6) Public Blog Cleanliness Gate
- [ ] Single H1 present
- [ ] No admin labels leaked
- [ ] No schema/debug/operator notes leaked

### 7) QA Report Completeness Gate
- [ ] Pass/fail by category
- [ ] Blockers + severity
- [ ] Fix list with owner/action
- [ ] Final status + rationale

### 8) Meta Phase/Status Gate
- [ ] `workflow_phase` valid
- [ ] `workflow_status` valid
- [ ] `blockers` accurate
- [ ] `last_phase_update_utc` valid ISO UTC

### 9) Conversion/Growth Optimization Gate
- [ ] Early soft CTA in first half
- [ ] Strong CTA near end
- [ ] Booking confidence details present
- [ ] Friction reducers included (logistics/policy clarity)

### 10) End-User Readiness Gate
- [ ] Readability/scannability passes
- [ ] Practical traveler expectations explained
- [ ] Next action obvious and direct-booking oriented
