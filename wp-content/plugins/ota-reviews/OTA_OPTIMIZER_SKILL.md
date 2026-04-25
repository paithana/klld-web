# 🤖 Skill: OTA Content Optimizer

This skill enables the agent to synchronize and optimize tour content from Online Travel Agencies (OTAs) like GetYourGuide and Viator, using the TopRank methodology for SEO and high-conversion copy.

## 🎯 Objectives
- Fetch raw activity data (descriptions, highlights, itineraries) from OTA APIs.
- Transform raw OTA data into high-quality, SEO-optimized WordPress content.
- Ensure all content follows E-E-A-T (Experience, Expertise, Authoritativeness, Trustworthiness) guidelines.

## 🛠 Available Tools
- `wp ota-reviews sync-content --post_id=<ID>`: Fetches and applies raw content from GYG.
- `wp ota-reviews sync-calendar --post_id=<ID>`: Synchronizes availability and pricing.

## 📋 Optimization Workflow

### Step 1: Data Ingestion
Run the sync command to pull the latest baseline data from the OTA.
```bash
wp ota-reviews sync-content --post_id=<ID>
```

### Step 2: Gap Analysis & Audit
Compare the synced OTA content with the current WordPress tour page. Identify:
- Missing highlights or inclusions.
- Outdated pricing or duration.
- Weak SEO signals (low keyword density, generic titles).

### Step 3: AI-Enhanced Writing (TopRank Style)
Rewrite the following sections using Gemini:
- **Product Title:** Optimize for search intent (e.g., "Similan Islands Early Bird Tour from Khao Lak").
- **Description:** Move beyond the generic OTA copy. Focus on the "Last Click" principle—provide all the info a traveler needs.
- **Highlights:** Use power verbs and benefit-driven bullet points.
- **FAQ:** Generate FAQs based on common traveler questions found in OTA reviews.

### Step 4: Technical Validation
- Ensure all HTML tags are valid.
- Verify that `tours_program` (itinerary) is correctly formatted for the Traveler theme.
- Check that `st_tour_availability` is updated to prevent booking errors.

## 🚦 Quality Gate
- **Helpful Content:** Does this description answer "Why should I book this specific tour?"
- **Conversion focus:** Is the "Included/Excluded" section clear and honest?
- **Rich Results:** Does the content support Schema.org markup (automatically handled by the plugin but needs good text)?

---
*Inspired by the TopRank Framework for AI-Driven SEO.*
