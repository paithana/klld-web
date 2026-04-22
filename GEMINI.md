# ♊ Gemini CLI - Workspace Context & Session History

## 🚀 Project Overview: Khao Lak Land Discovery (KLLD)
This workspace focuses on the review synchronization and optimization for the **Traveler** theme on `khaolaklanddiscovery.com`.

## 🛠 Work Completed (April 2026)

### 1. **OTA Review Synchronization & Mapping**
*   **TripAdvisor (TA):** 
    *   **Count:** 88 reviews currently imported.
    *   **Workflow:** Browser-based scraper (`ta_browser_scraper.js`) is used to bypass DataDome bot protection. Data is received via `ta_receiver.php`.
*   **GetYourGuide (gyg):**
    *   **Count:** **2,765 unique reviews**.
    *   **Integrity:** Restored to origin tour assignments and excluded from remapping as per the "don't filter" directive.
*   **Google (gmb):**
    *   **Count:** **800 unique reviews**.
    *   **Consolidation:** Standardized from multiple JSON sources.

### 2. **Global Remapping & Logic Fixes**
*   **Advanced Mapping:** Created a global remapping engine that uses **Tour Itineraries (Programmablauf)** and keywords (`_ota_keywords`) to precisely link reviews to the correct WordPress posts.
*   **GYG Exclusion:** The remapping engine is configured to **ignore** all reviews where the `ota_source` is `gyg`, preserving their original tour assignments.
*   **Duplicate Removal:** Successfully identified and deleted over **4,600 redundant reviews**, keeping only unique entries per author/content/tour.

### 3. **Frontend & UX Optimizations**
*   **Order:** Reviews are now sorted by **Date (Descending)** by default.
*   **Performance:**
    *   **Infinite Scroll (Autoload):** Implemented for the first **50 reviews** using `IntersectionObserver`.
    *   **"Load More" Button:** Replaces standard pagination after the 50-review limit is reached.
    *   **Lazy Loading (`loading="lazy"`)** for all review avatars and gallery photos.
*   **Interaction:** The "Write a Review" button smoothly toggles form visibility directly on the page.

## 📁 Key Tools & Scripts
*   `wp-content/plugins/ota-reviews/data/`: Consolidated raw data directory.
*   `wp-content/plugins/ota-reviews/ta_browser_scraper.js`: Browser console script for TripAdvisor.
*   `wp-content/plugins/ota-reviews/import_ta_reviews.php`: Import & matching logic for TA.
*   `wp-content/plugins/ota-reviews/import_gmb_reviews.php`: Unified importer for consolidated GMB JSON.

## 📡 Database Mappings (Final)
*   `gmb` = Google (Consolidated from all JSON sources)
*   `TA` = TripAdvisor (Verified scraped source)
*   `gyg` = GetYourGuide (Restored to origin tours, remapping excluded)
*   `vt` = Viator (System ready, awaiting data)

---
*Last Updated: April 21, 2026*
