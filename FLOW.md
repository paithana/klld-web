# Review Sync & Mapping Workflow (FLOW.md)

## 1. Scraping & Data Ingestion
*   **TripAdvisor (TA):** 
    *   **Method:** Browser-side console script (`ta_browser_scraper.js`).
    *   **Pipeline:** Scraper -> `ta_receiver.php` -> `ta_scraped_reviews.json` (stored in `/data/source_ta.json`).
*   **Google (GMB):** 
    *   **Method:** JSON data import (`source_gmb_legacy.json`, `source_gmb_new.json`).
*   **GetYourGuide (GYG):** 
    *   **Method:** SQL Import (`source_gmb.sql`).
    *   **Note:** GYG reviews are excluded from automated remapping.

## 2. Processing & Mapping
*   **Mapping Engine:** `remap_all_automated.php`.
*   **Matching Logic:** 
    *   Uses keyword-based matching (`_ota_keywords`) and tour itinerary text matching.
    *   Only processes `gmb` and `ta` sources; `gyg` is skipped.
*   **Post-Processing:**
    *   `remove_duplicates.php`: Identifies and deletes duplicate reviews based on author, content, and post ID.
    *   `update_review_mapping.php`: Used for manual override of tour assignments via CLI.

## 3. Frontend & UX
*   **Sorting:** `functions.php` forces `comment_date` DESC order for all reviews.
*   **Loading:** 
    *   **Infinite Scroll:** Autoloads top-level reviews up to 50 entries using `IntersectionObserver` in `review.php`.
    *   **Nested Replies:** Child comments (replies) are rendered recursively as sub-elements under their respective parent reviews with visual indentation.
    *   **Load More:** Manual button displayed for top-level counts > 50.
*   **Lazy Loading:** Filters applied to `wp_get_attachment_image_attributes` for all images and specifically on review-list avatars.

## 4. Verification Flow
1.  **Run Importers:** `import_ta_reviews.php` (for TA) and `import_gmb_reviews.php` (for Google).
2.  **Run Remapping:** `php remap_all_automated.php`.
3.  **Run Cleanup:** `php remove_duplicates.php`.
4.  **Database Check:** Verify meta counts via `ota_source` in `wp_commentmeta`.
