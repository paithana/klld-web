# TripAdvisor Supplier Profile Scraper (1200+ Reviews)

Because of TripAdvisor's DataDome protection, server-side scraping via CLI is blocked. To process all 1,200+ reviews from your supplier profile, please follow these steps:

## 1. Browser Scraper
1. Open your TripAdvisor Supplier Profile in a Chrome-based browser.
2. Open the **Console** (F12).
3. Copy/paste the script from `wp-content/plugins/ota-reviews/ta_browser_scraper.js` and run it.
4. It will send batches to `ta_receiver.php`.

## 2. Import & Match
Once the scraping is complete (you'll see the files in `wp-content/plugins/ota-reviews/data/source_ta.json`), run the following command in your terminal:

```bash
php wp-content/plugins/ota-reviews/import_ta_reviews.php
```

This will automatically fuzzy-match the titles from the "Review of:" field against your WordPress tour titles and import them as correctly attributed TripAdvisor (TA) reviews.
