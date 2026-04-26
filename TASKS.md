# ✅ OTAs Manager - Tasks & Todo List

## 🛠 Active Tasks (v2.2 Maintenance)
- [ ] **SFTP Connection Debug:** Investigate SFTP login rejection for Google Things to Do feed push.
- [ ] **Mobile Touch Optimization:** Further refine the "Manage" panel button sizes for very small screens (< 360px).
- [ ] **Cache Script Scheduling:** Coordinate with server admin to set `cron-cache.sh` to run every 15 minutes.
- [ ] **Scrape Script Scheduling:** Coordinate with server admin to set `cron-scrape.sh` to run twice daily.

## 🚀 Future Roadmap (v3.0)
- [ ] **Phase 1: Bidirectional Content Sync**
    - [ ] Integrated Tour Title/Description fetch from GYG/Viator.
    - [ ] Automated OTA photo import to WP Media Library.
- [ ] **Phase 2: Calendar & Availability Sync**
    - [ ] iCal feed support for external platforms.
    - [ ] Real-time availability block between Traveler and OTAs.
- [ ] **Phase 3: Automation & Analytics**
    - [ ] AI Itinerary Generator.
    - [ ] Automated Review Replies via OTA APIs.

## 🧹 Technical Debt
- [ ] **File Cleanup:** Remove legacy `ta_fetch_one.php` and `ta_omkar_sync.php` after verifying v2.2 stability.
- [ ] **Code Refactoring:** Consolidate redundant styling between `review_editor.php` and `review_tool.php` into a single CSS file.
