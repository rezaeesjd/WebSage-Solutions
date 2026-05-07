# AGENTS.md

## Purpose
This repository section is used to generate SEO and conversion-focused content for tours, day trips, and local experiences.

The main business goal is to increase direct bookings on the website while still benefiting from OTA visibility on Viator and TripAdvisor.

## Primary objective
For each tour title provided by the user, create a dedicated folder and generate structured SEO content assets that are ready for reuse in a CMS.

## Folder creation rule
Create one folder inside `/tours/` for each tour title.

Folder name format:
- lowercase
- kebab-case
- based on the canonical tour title

Example:
`Chianti Wine Tour from Florence` → `/tours/chianti-wine-tour-from-florence/`

## Required files inside each tour folder
1. `brief.md`
2. `keywords.md`
3. `blog-post.md`
4. `faq.md`
5. `meta.json`
6. `internal-links.md`

## Strategy rules
- Prioritize conversions over traffic volume.
- Prioritize long-tail, booking-intent keywords before broad informational keywords.
- Use a hybrid publishing model: 1 booking-intent post per week, 1 informational/supporting post per week, and 1 weekly refresh of older pages.
- Build content around a repeatable cluster for each tour:
  - 1 main landing page or landing-blog hybrid
  - 2 comparison posts
  - 2 informational posts
  - 1 seasonal or FAQ support post

## Funnel rules
### TOFU
Use informational content for travelers researching destinations, timing, and ideas.

### MOFU
Use comparison and decision-support content for travelers comparing tour styles, routes, and booking options.

### BOFU
Use highly commercial content for travelers ready to book a specific experience.

## SEO rules
For each tour generate:
- 1 primary keyword with booking intent
- 5 to 10 long-tail booking-intent keywords
- 3 to 5 informational keywords
- 4 to 6 title variations
- 1 short keyword-optimized slug
- 1 page title
- 1 meta description

## Blog post rules
The main post must be a short, high-converting landing-page hybrid, not a long generic blog article.

Each `blog-post.md` must include:
- Page Title
- URL Slug
- Meta Description
- H1
- Hook paragraph
- Main value section
- Soft CTA
- Strong CTA block
- FAQ or supporting section
- Internal linking suggestions

## Writing rules
- Write for real travelers, not search engines.
- Keep the tone friendly, persuasive, clear, and natural.
- Avoid keyword stuffing and generic filler.
- Avoid robotic phrasing.
- Focus on practical decision-making and booking confidence.
- Keep paragraphs relatively short and easy to scan.

## Trust and OTA positioning
- Encourage direct booking on the website first.
- OTA platforms such as Viator and TripAdvisor may be referenced only as trust signals, review sources, or secondary discovery channels.
- Do not position OTAs as the primary conversion path unless explicitly requested.

## Internal linking rules
Each content asset should suggest links to:
- the main tour page
- one related blog or guide
- one FAQ or destination information page
- one booking or contact page

Preferred flow:
`informational post -> comparison post -> tour page -> booking`

## Content safety rules
- Do not invent facts.
- Do not invent pricing, durations, inclusions, departure times, meeting points, or review counts.
- If required business details are missing, use clearly labeled placeholders.
- Do not make unverifiable claims such as "best in the city" unless supported by provided evidence.

## Reusability and layout rules
- Keep structure consistent across all generated posts for easy CMS upload.
- Reuse the same CTA block pattern unless the user asks for a variation.
- Favor a consistent design system with identical sections and only tour-specific content swapped in.

## CTA template
### Standard CTA block
**Title:** Ready to book your [tour name]?

**Line:** See availability, full details, and secure your spot directly on our website.

**Button:** Check Availability

## Definition of done
A generation task is complete only when:
- the tour folder is created
- all required files are generated
- filenames follow the naming convention
- content is ready to paste into the CMS with minimal editing
- the post supports direct booking as the primary goal
