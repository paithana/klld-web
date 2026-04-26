# 🐛 OTAs Manager - Bug & Error Tracker

## 🚨 Known Issues & Active Bugs (v2.2)

### 1. SFTP Connection Rejection
*   **Module:** GTTD Feed Push (`gttd_sftp_push.php`)
*   **Description:** The system successfully generates the `tours_feed.tmp.xml` feed but fails during the SFTP upload to `partnerupload.google.com`.
*   **Error Log:** `SSH Key login rejected by server for mc-sftp-5520609361.` followed by `Error: All SFTP login attempts failed`.
*   **Status:** Pending Investigation.
*   **Next Steps:** Verify the public key is correctly registered in the Google Partner Dashboard and that the private key path in the script has the correct `0600` permissions and formatting.

### 2. TripAdvisor / Viator 403 Forbidden
*   **Module:** Sync Manager (`ota_sync.php`)
*   **Description:** High-volume automated requests to TripAdvisor/Viator endpoints occasionally return `403 Forbidden`.
*   **Cause:** Cloudflare / Bot protection blocking the server's IP address or recognizing the script's User-Agent.
*   **Workaround:** The system has been modified to use the Omkar scraper or manual proxy imports.
*   **Next Steps:** Consider implementing rotating residential proxies or further randomizing the delay/User-Agent strings if the issue persists during manual syncs.

### 3. Large Dataset Auto-Match Timeout
*   **Module:** Review Editor (`klld_find_best_matches`)
*   **Description:** If a user selects all 100 items on a page and clicks "Auto Match", the server may timeout (504 Gateway Timeout) on lower-tier hosting plans.
*   **Cause:** The `similar_text()` PHP function running inside a nested loop for thousands of combinations is highly CPU intensive.
*   **Workaround:** The timeout limit was artificially increased to `set_time_limit(120)` in v2.2, and a fast-match exact string check was added.
*   **Next Steps:** If timeouts continue, refactor the JavaScript to send the Auto-Match requests in batches of 10 instead of 100 at once.

---
*If you encounter new errors, please append them to this document with the Module name and steps to reproduce.*