# ♊ Gemini CLI - Workspace Context & Session History

## 🚀 Project Overview: Khao Lak Land Discovery (KLLD)
This workspace focuses on the review synchronization and optimization for the **Traveler** theme on `khaolaklanddiscovery.com`.

## 🛠 Work Completed (April 2026)

### 1. **OTA Review Synchronization & Matching**
*   **TripAdvisor (TA):** 
    *   Identified and imported **720 TripAdvisor reviews**.
    *   **600+ reviews** were sourced via a browser-based scraper (`ta_browser_scraper.js`) to bypass DataDome bot protection.
    *   Used a **Fuzzy Matching Algorithm** (Jaccard Similarity) to link reviews to tours based on the "Review of: [Tour Name]" metadata.
*   **GetYourGuide (gyg):**
    *   Imported and verified **5,614 reviews** from `import_all_reviews.sql`.
    *   Ensured correct attribution by mapping `@gyg.com` and `@getyourguide.com` emails.
*   **Google (gmb):**
    *   Imported and mapped reviews from `gmb_test_data.json`.
    *   Cleaned up and re-verified sources to avoid cross-platform mislabeling.

### 2. **Global Remapping & Logic Fixes**
*   **Advanced Mapping:** Created a global remapping engine (`remap_reviews.php`) that uses **Tour Itineraries (Programmablauf)** and keywords (`_ota_keywords`) to precisely link reviews to the correct WordPress posts.
*   **GYG Exclusion:** The remapping engine is configured to **ignore** all reviews where the `ota_source` is `gyg`, preserving their original tour assignments.
*   **Title Cleanup:** Automatically removed the string `"Expert tour from Gyg"` from over **2,300 review titles** to improve frontend appearance.
*   **Duplicate Removal:** Identified and deleted **3,642 duplicate reviews**, keeping only the unique newest entries per author/content/tour.

### 3. **Frontend & UX Optimizations**
*   **Order:** Reviews are now sorted by **Date (Descending)** by default.
*   **Performance:**
    *   **Infinite Scroll (Autoload):** Implemented infinite scroll for the first **50 reviews** using `IntersectionObserver`.
    *   **"Load More" Button:** After 50 reviews, the system switches to a manual "Load More" button to maintain page stability.
    *   **Lazy Loading (`loading="lazy"`)** for all review avatars and gallery photos.
*   **Interaction:**
    *   **Write a Review:** The "Write a Review" button now smoothly toggles the review form visibility directly on the page.
*   **Branding:** Updated the site logo to the high-quality original (**Attachment 14501**) and fixed the directory paths for OTA badges.

## 📁 Key Tools & Scripts
*   `/wp-content/plugins/ota-reviews/ta_browser_scraper.js`: Browser console script for scraping TripAdvisor.
*   `/wp-content/plugins/ota-reviews/ta_receiver.php`: Endpoint for receiving scraped data.
*   `/wp-content/plugins/ota-reviews/import_ta_reviews.php`: Server-side import & matching logic.
*   `remap_reviews.php`: One-time global remapping tool.
*   `remove_duplicates.php`: Cleans up redundant reviews in the database.

## 📡 Database Mappings (Final)
*   `gmb` = Google (from `import_all_reviews.sql` and other GMB imports)
*   `TA` = TripAdvisor (from browser scraper `ta_browser_scraper.js`)
*   `gyg` = GetYourGuide (currently no reviews with this source)
*   `vt` = Viator (system ready, no data found)

---
*Last Updated: April 21, 2026*
