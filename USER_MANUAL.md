# 📖 OTAs Manager - User Manual (v2.2)

Welcome to the **OTAs Manager Command Center**. This suite of tools allows you to synchronize, moderate, and AI-enhance your tour reviews across multiple platforms (GetYourGuide, TripAdvisor, Viator, Google).

## 🧭 1. Dashboard Overview
Navigate to **OTAs Manager** in the WordPress sidebar to access the Command Center. 
- **Tool Cards:** Click any card (Sync Manager, Review Editor, etc.) to open that specific tool.
- **Activity Log:** View the 10 most recently imported or updated reviews across all platforms.
- **SFTP Status:** Expand the "SFTP Connection & Feed Settings" to check the health of your Google Things to Do integration.

## 🔄 2. Sync Manager
*Purpose: Map your WordPress tours to their respective OTA IDs and trigger synchronizations.*
- **Sync & Import Tab:** Start manual background syncs for individual platforms.
- **Global Mapping Tab:** Search for your tours and input the corresponding Product IDs from GYG, Viator, TripAdvisor, and Google Business. The background engine (`cron-scrape.sh`) uses these IDs to fetch new reviews.

## ✍️ 3. Review Editor
*Purpose: Moderate, remap, and clean up your review data.*
- **Search & Filter:** Use the top bar to search by author or keyword. Change the "Items per page" (20, 50, 100) or filter by language (e.g., "Default EN Only") to focus your moderation.
- **Accordion View:** The table shows a clean summary. Click **Manage ▾** on any row to open its full details.
- **Searchable Mapping:** In the expanded panel, click the "Tour Mapping" input to instantly search for and assign a different tour to the review.
- **AI Assisted Moderation:** In the expanded panel, use the buttons to:
  - **🛡 AI Check:** Verify if the text was AI-generated.
  - **✨ Fix Text:** Send text to a grammar checker.
  - **💬 Reply:** Generate a professional response template.
- **Bulk Actions:** Check multiple boxes on the left to reveal the Bulk Action Bar at the top. You can assign all selected reviews to a single tour using the searchable dropdown, or click "✨ Auto Match" to let the system guess the best tour based on the review text.

## 🤖 4. AI Review Writer
*Purpose: Automatically generate catchy headlines for reviews that lack titles.*
- **Configuration:** Enter your OpenAI API key in the top box and click "Save".
- **Batch Generation:** Click "✨ Batch Generate (OpenAI)" to automatically create titles for *all* reviews missing them.
- **Manual Generation:** For a specific review, click:
  - **OpenAI (Auto):** Generates and saves a title immediately.
  - **Ahrefs Title / Sum:** Opens Ahrefs' free web tools in a new tab with the review text pre-filled.
  - **Humanize / IG Post / Add 🎨:** Advanced marketing options opening in new Ahrefs tabs.

## 🌱 5. Review Generator
*Purpose: Seed new tours with authentic review templates.*
- Select one or multiple target tours.
- Use the search box to find specific templates (e.g., search "guide" or "elephant").
- Set how many reviews to generate per tour, check "Auto-Approve", and click "Launch Batch Seeding".

## 📥 6. Export Reviews
*Purpose: Download database backups of your reviews and tour data.*
- Select **JSON** (for analysis/development) or **SQL** (for migrating to a new database).
- The export deeply links reviews to their full tour descriptions (`wp_posts` and `wp_st_tours`).

## ⚙️ 7. System Maintenance
- Your system relies on two cron jobs configured on your server:
  - `cron-cache.sh`: Clears website caches to keep things fast.
  - `cron-scrape.sh`: Fetches new reviews and pushes feeds to Google.
