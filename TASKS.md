# Project Tasks

This file tracks pending and future tasks for the `khaolaklanddiscovery.com` project.

## 📝 To-Do

- [ ] **Import TripAdvisor Reviews:**
  - [ ] Scrape the ~1200 reviews from the TripAdvisor supplier profile using the browser script.
  - [ ] Run the `import_ta_reviews.php` script to match and import them.

- [ ] **Frontend Verification:**
  - [ ] Manually test the "Load More" and "Autoload" functionality on a tour with a large number of reviews.
  - [ ] Verify that all OTA badges (GMB, TA, GYG) display correctly on the user end.

- [ ] **Database Cleanup:**
  - [ ] Investigate the origin of the 108 older TripAdvisor reviews to see if they can be merged or updated.

- [ ] **New Tasks:**
  - [ ] *Add new tasks here...*

## ✅ Completed
- Remapped all existing reviews based on itinerary and keywords.
- Corrected `ota_source` attribution for all review platforms.
- Removed thousands of duplicate reviews.
- Implemented "Load More" and Autoload for the review list.
- Fixed site logo and OTA avatar paths.
