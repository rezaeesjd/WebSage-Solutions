# AGENTS.md

## Purpose
This repository section is used to generate SEO and conversion-focused content for tours, day trips, and local experiences.

The main business goal is to increase direct bookings on the website while still benefiting from OTA visibility on Viator and TripAdvisor.

The purpose of each post is to move the traveler one step closer to booking. Do not create content only to fill a blog.

## Brand context
The brand name is **Milano Adventures**.

When generating content:
- write in a friendly, clear, travel-focused brand voice
- prioritize trust, ease, and memorable local experiences
- keep the tone practical and persuasive rather than luxury-heavy or overly formal
- use the brand name naturally only where it helps trust or conversion

## Booking link placeholders
Use these placeholders when a booking link is needed in generated content:
- Website booking page: `{{WebsiteLink}}`
- TripAdvisor booking page: `{{TripAdvisorLink}}`
- Viator booking page: `{{ViatorLink}}`

Prefer the website link as the main CTA. TripAdvisor and Viator links may be used only as secondary trust or alternate booking references when appropriate.

## Primary objective
For each tour title provided by the user, create a dedicated folder and generate structured SEO content assets that are ready for reuse in the WebPublisherSystem platform.

This file is a production workflow, not only a policy file. Follow the workflow exactly.

## Working model
- The main system folder is `/WebPublisherSystem/`.
- This instruction file lives in `/WebPublisherSystem/content-system/`.
- Generated tour assets must live inside `/WebPublisherSystem/content-system/tours/`.
- Reusable templates may live inside `/WebPublisherSystem/templates/`.
- Platform code lives inside `/WebPublisherSystem/platform/`.
- If a needed folder does not exist, create it.

## Wait rule
Do not generate a sample post or create a new tour folder until the user provides a tour topic or tour title.

If the user asks only for strategy, templates, or system setup, provide or update the system files without generating a tour-specific content package.

## Tour folder creation rule
Create one folder inside `/WebPublisherSystem/content-system/tours/` for each tour title.

Folder name format:
- lowercase
- kebab-case
- based on the canonical tour title
- no special characters except hyphens

Example:
`Cinque Terre Full-Day Tour from Milan` → `/WebPublisherSystem/content-system/tours/cinque-terre-full-day-tour-from-milan/`

## Required files inside each tour folder
1. `brief.md`
2. `keywords.md`
3. `blog-post.md`
4. `faq.md`
5. `meta.json`
6. `internal-links.md`
7. `automation-notes.md`

## Master workflow
For each tour request, complete the work in this order.

### Step 1: Strategy
Define a clear and practical SEO and conversion strategy for the specific tour topic.

The strategy output must include:
- posting frequency recommendation for that tour or content cluster
- recommended content types
- funnel mapping across TOFU, MOFU, and BOFU
- internal linking strategy from blog content to tour pages and booking pages
- how Viator and TripAdvisor visibility can support direct bookings indirectly
- whether long-tail or higher-volume keywords should be prioritized first and why
- a repeatable production approach for scaling similar tour content

### Step 2: Keyword clustering
Generate a keyword cluster for the specific tour topic.

The keyword output must include these sections:
- Primary keyword: 1 high-intent keyword
- Long-tail booking-intent keywords: 5 to 10 keywords
- Informational keywords: 3 to 5 keywords
- Comparison keywords: 2 to 5 keywords when relevant
- Title variations: 4 to 8 title options

Group keywords by intent and label them clearly.

### Step 3: Reusable blog template
Use the standard blog structure defined in this file.

### Step 4: Sample post execution
Generate one fully optimized landing-blog hybrid post for the provided tour topic.

### Step 5: Automation notes
Explain how this template and workflow can be reused weekly or daily, scaled with AI tools, connected to CMS or scheduling systems, and kept visually consistent.

## Strategy rules
- Prioritize conversions over traffic volume.
- Prioritize long-tail, booking-intent keywords before broad informational keywords.
- Use a hybrid publishing model by default: 1 booking-intent post per week, 1 informational or comparison post per week, and 1 weekly refresh of older pages.
- Build content around a repeatable cluster for each tour:
  - 1 main landing page or landing-blog hybrid
  - 2 comparison posts
  - 2 informational posts
  - 1 seasonal, FAQ, or support post
- Only recommend daily posting when there is enough tour inventory, destination breadth, or operational capacity to maintain quality.

## Funnel rules
### TOFU
Use informational content for travelers researching destinations, timing, options, and general trip ideas.

### MOFU
Use comparison and decision-support content for travelers comparing routes, transport options, tour styles, timing, and value.

### BOFU
Use highly commercial content for travelers ready to book a specific experience.

## SEO rules
For each tour request generate:
- 1 primary keyword with booking intent
- 5 to 10 long-tail booking-intent keywords
- 3 to 5 informational keywords
- 2 to 5 comparison keywords when relevant
- 4 to 8 title variations with clear angles
- 1 short keyword-optimized slug
- 1 page title
- 1 meta description

## Title rules
- Put the primary keyword near the beginning of the title when natural.
- Add a clear benefit or conversion angle, such as easy, guided, full-day, stress-free, scenic, private, small-group, or direct booking.
- Avoid very long titles.
- Avoid overusing words like "best", "top-rated", or "number one" unless the user provides proof.
- Do not make exaggerated or unverifiable claims.

## Title variation rules
Include title variations across multiple angles when possible:
- direct booking angle
- convenience or stress-free angle
- comparison angle
- seasonal or timing angle
- value or experience angle

## Blog post rules
The main post must be a short, high-converting landing-page hybrid, not a long generic blog article.

### Ideal structure order
Each `blog-post.md` must include content in this order:
1. Page Title
2. URL Slug
3. Meta Description
4. H1
5. Hook paragraph
6. Main value section
7. Soft CTA
8. Supporting section or FAQ
9. Strong CTA block
10. Internal linking suggestions

### Length and style rules
- Target length for the main post: approximately 500 to 900 words unless the user requests otherwise.
- The hook paragraph should be short, emotionally clear, and conversion-aware.
- The hook should also be suitable for reuse as a short summary or meta-style introduction.
- The content should be easy to scan, with relatively short paragraphs and subheadings.
- The post should feel like a blog and landing page hybrid.
- Avoid long generic destination history unless directly useful for booking decisions.

### Section guidance
#### Page Title
Must contain the main keyword plus a conversion or benefit angle.

#### URL Slug
Must be short, readable, and keyword-focused.

#### Meta Description
Must be concise, persuasive, and suitable for search snippets.

#### H1
Must use the primary keyword naturally.

#### Hook paragraph
Should quickly answer why this tour matters, who it is for, or what problem it solves.

#### Main value section
Should explain:
- why the tour is worth considering
- who the experience is best for
- what makes it attractive or easier than alternatives
- what the traveler should expect at a high level

#### Soft CTA
Place after the main value section.
The soft CTA should invite the user to view details or check availability without sounding overly aggressive.

#### Supporting section or FAQ
Add brief practical value such as planning tips, what to know before booking, comparison context, or frequently asked questions.

#### Strong CTA block
Place after the supporting section.
The strong CTA should clearly guide the reader toward direct website booking.

#### Internal linking suggestions
Provide exact link targets by page type, such as:
- main tour page
- related destination guide
- comparison article
- FAQ or booking/contact page

## Conversion checklist
Every generated `blog-post.md` must include:
- one direct website CTA in the first half of the post or immediately after the main value section
- one strong website CTA near the end
- booking confidence details from the product input, such as duration, meeting point, group type, included items, excluded items, languages, or accessibility notes
- a clear "who this tour is best for" or equivalent section
- a clear "what to know before booking" or equivalent section
- direct booking as the preferred action using `{{WebsiteLink}}`
- TripAdvisor and Viator only as secondary trust or alternate booking references when useful

## Writing rules
- Write for real travelers, not search engines.
- Keep the tone friendly, persuasive, clear, and natural.
- Avoid keyword stuffing and generic filler.
- Avoid robotic phrasing.
- Focus on practical decision-making and booking confidence.
- Keep paragraphs relatively short and easy to scan.
- Favor clarity and usefulness over content length.
- Do not create content simply to publish something. Each section should help the traveler understand, compare, trust, or book the tour.

## Trust and OTA positioning
- Encourage direct booking on the website first.
- OTA platforms such as Viator and TripAdvisor may be referenced only as trust signals, review sources, or secondary discovery channels.
- Do not position OTAs as the primary conversion path unless explicitly requested.
- Where useful, mention that travelers may also recognize the business from trusted marketplaces, but the preferred booking action should remain the website.

## Internal linking rules
Each content asset should suggest links to:
- the main tour page
- one related blog or guide
- one FAQ or destination information page
- one booking or contact page

Preferred flow:
`informational post -> comparison post -> tour page -> booking`

### Internal URL safety
- Do not invent final internal URLs unless the user provided them.
- If the real internal URL is unknown, use a clear placeholder such as `{{MilanDayTripsHubLink}}`, `{{RelatedGuideLink}}`, `{{ComparisonPostLink}}`, or `{{ContactPageLink}}`.
- If suggesting a page that does not exist yet, label it as a "Suggested future page".
- The main booking link should use `{{WebsiteLink}}` unless the user provides a final URL.

## Content safety rules
- Do not invent facts.
- Do not invent pricing, durations, inclusions, departure times, meeting points, or review counts.
- If required business details are missing, use clearly labeled placeholders.
- Do not make unverifiable claims such as "best in the city" unless supported by provided evidence.
- Do not fabricate review quotes, rankings, awards, or customer numbers.

## Source-facts-only rules for tours
Only mention the following details if they are provided in the product input or by the user:
- exact itinerary stops
- tour duration
- meeting point or pickup rules
- end point
- included items
- excluded items
- group size or private/shared status
- languages
- accessibility notes
- health restrictions
- weather or seasonal limitations
- guide/driver details
- booking links

If a detail is missing, do not guess it. Use a placeholder or omit it.

When the product input contains both a broad marketing description and a more specific structured itinerary, follow the structured itinerary for exact claims.

## File-specific output rules
### `brief.md`
Must summarize:
- tour title
- traveler intent
- target funnel stage
- conversion goal
- business positioning
- assumptions and missing inputs

### `keywords.md`
Must include clearly labeled sections for:
- primary keyword
- long-tail booking-intent keywords
- informational keywords
- comparison keywords when relevant
- title variations

### `blog-post.md`
Must follow the exact blog structure defined in this file.

### `faq.md`
Must include practical pre-booking questions and answers that help reduce hesitation.

### `meta.json`
Must include structured fields for:
- brand
- product_reference_code
- canonical_tour_title
- page_title
- slug
- meta_description
- primary_keyword
- funnel_stage
- cta_primary
- website_link
- tripadvisor_link
- viator_link
- publish_status
- human_review_required

Use `"publish_status": "draft"` and `"human_review_required": true` unless the user explicitly says otherwise.

### `internal-links.md`
Must suggest internal links by page type and explain why each suggested link matters. Use placeholders or "Suggested future page" labels when final URLs are not provided.

### `automation-notes.md`
Must explain:
- how to reuse this structure weekly or daily
- how to scale production with AI assistance
- how to keep the design and section order consistent
- how to adapt the same template to similar tour topics

## Reusability and layout rules
- Keep structure consistent across all generated posts for easy CMS upload.
- Reuse the same CTA block pattern unless the user asks for a variation.
- Favor a consistent design system with identical sections and only tour-specific content swapped in.
- Keep the same section order across posts unless the user requests a different structure.

## CTA template
### Standard CTA block
**Title:** Ready to book your [tour name]?

**Line:** See availability, full details, and secure your spot directly on our website.

**Button:** Check Availability

### Soft CTA example
See the full tour details, inclusions, and current availability before you book.

## Automation guidance rules
When generating automation notes, include guidance on:
- batching similar tour topics together
- using reusable prompts and CMS fields
- using a fixed page layout or section template
- scheduling posts and refreshes consistently
- refreshing high-value pages regularly instead of publishing only new pages
- preserving human review before publishing factual business details

## Definition of done
A generation task is complete only when:
- the correct tour folder is created in `/WebPublisherSystem/content-system/tours/`
- all required files are generated
- filenames follow the naming convention
- content follows the five-step workflow in this file
- the post supports direct booking as the primary goal
- the conversion checklist is satisfied
- final internal URLs are not invented; unknown URLs use placeholders or are labeled as suggested future pages
- the content is ready to paste into the WebPublisherSystem platform with minimal editing
