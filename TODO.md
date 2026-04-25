# 📝 Khao Lak Land Discovery - TODO List

## 🔴 High Priority (Errors & Warnings)

### 1. **Google Tours Feed Warning**
- **File:** `wp-content/plugins/ota-reviews/google-tours-feed.php`
- **Issue:** `PHP Warning: Undefined array key "availability"` on line 305/326.
- **Root Cause:** The `availability` key is missing from the initial `$product` array definition (Line 150+).
- **Task:** [x] Add `'availability' => 'in_stock'` to the `$product` array.

### 2. **Broken Include in Mapper**
- **File:** `wp-content/plugins/ota-reviews/misc/ota_auto_mapper.php`
- **Issue:** `PHP Fatal error: require_once(...): Failed to open stream` on line 5.
- **Task:** [x] Verify and fix the include path for `traveler-childtheme/inc/ota-tools/ota_auto_mapper.php`.

### 3. **Database Integrity**
- **Issue:** "object Object" reviews occasionally appear after failed syncs.
- **Task:** [x] Automate the cleanup process or investigate why `json_decode` fails silently during sync.

---

## 🟡 Medium Priority (Synchronization & Scraping)

### 4. **GMB Scraper Detection**
- **Issue:** Google Maps often detects and blocks the server's IP during Puppeteer execution.
- **Task:** 
    - [ ] Implement a localized proxy or residential rotating proxy.
    - [ ] Refine the multi-language consent bypass logic.

### 5. **Viator Sync Blocking**
- **Issue:** Viator (TripAdvisor OTA) is currently blocking scraper requests.
- **Task:** Investigate API-based alternatives or more advanced stealth techniques.

---

## 🔵 Low Priority (Code Debt & UX)

### 6. **Imagify TODOs**
- **Files:** `wp-content/plugins/imagify/assets/...`
- **Tasks:**
    - [x] `options.css:181`: Remove unused table lines.
    - [x] `pricing-modal.js:228`: Change waterfall request handling.

---

## ✅ Completed Tasks (Recent Fixes)
- [x] Migrated `shell_exec` to `proc_open` in `class-ota-cli.php` and `ta_scrape_filter.php`.
- [x] Fixed `round(null)` deprecated warning in `ota_sync.php`.
- [x] Resolved `Undefined array key` for `reviewer_name` in `import_ta_reviews.php`.
- [x] Added automated Consent Page bypass to GMB scraper.
