# 🗺 OTAs Manager - Future Roadmap (v3.0)

This document outlines the strategic roadmap for expanding the OTAs Manager from a review moderation suite into a full-scale Channel Management & Sync solution.

## 🚀 Phase 1: Bidirectional Content Sync (Pull/Push)
*Goal: Ensure tour descriptions, photos, and settings are identical across WP and OTAs.*

- [ ] **Pull Logic:**
    - Integrated fetch for Tour Title, Description, and Features from GYG/Viator.
    - Photo sync: Automatically import OTA tour images into the WordPress Media Library.
- [ ] **Push Logic:**
    - "Update OTA" button: Push WordPress content changes (e.g., price updates, itinerary changes) directly to OTA partner portals via API.
- [ ] **Conflict Resolution:** UI to show differences between WP and OTA content with "Accept Local" or "Accept Remote" toggles.

## 📅 Phase 2: Calendar & Availability Sync
*Goal: Real-time availability synchronization to prevent overbooking.*

- [ ] **iCal / API Integration:**
    - Support for `.ics` feed imports from external booking platforms.
    - Direct API integration with Traveler's `wp_st_availability` and `st_tour_availability` tables.
- [ ] **Two-Way Sync:**
    - When a booking occurs on the Website, instantly decrease availability on OTAs.
    - When an OTA booking is detected, block those dates on the WordPress Traveler calendar.
- [ ] **Pricing Sync:** Support for seasonal pricing adjustments pushed from WordPress to OTAs.

## 📊 Phase 3: Advanced Automation & Analytics
- [ ] **Real-time Booking Dashboard:** A unified view of all incoming bookings (Web + OTA).
- [ ] **AI Itinerary Generator:** Expand the Ahrefs integration to generate full tour itineraries based on keyword inputs.
- [ ] **Automated Review Replies:** Option to automatically post AI-generated replies back to TripAdvisor/GYG via API (where supported).

---
*Last Updated: April 2026*
