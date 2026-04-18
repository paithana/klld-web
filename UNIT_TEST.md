# OTA Integration Unit Tests

This document outlines the available test suites for the tour review synchronization system. All tests are designed to be run from the command line in the WordPress root directory.

## 🚀 Recommended Verification Flow

To ensure system health after an update, run the main system suite:

```bash
php test_system_suite.php
```

---

## 📋 Available Test Suites

### 1. System-Wide Verification
The primary suite for validating core logic and data integrity.
- **Command**: `php test_system_suite.php`
- **Coverage**: 
  - Date normalization logic (`normalize_date`).
  - String matching accuracy (`calculate_match_score`).
  - WPML translation propagation.
  - Database upsert guards.

### 2. Manual Import Simulation
Verifies that manually pasted JSON correctly propagates to all language versions.
- **Command**: `php test_manual_import.php`
- **Verification**: Checks for the existence of `man_ta_123` in the `commentmeta` table.

### 3. TripAdvisor API Diagnostics
Directly tests connectivity and credential validity for the TripAdvisor Content API.
- **Command**: `php test_tripadvisor_api.php`
- **Note**: Requires `_ta_api_key` to be set in the Review Manager.

### 4. Auto-Mapper (Discovery Mode)
Runs the discovery engine to find and map TripAdvisor/GYG IDs without a browser.
- **Command**: `php -d display_errors=1 -r "define('KLLD_TOOL_RUN', true); require 'wp-content/plugins/ota-reviews/ota_auto_mapper.php';"`
- **Log File**: `wp-content/plugins/ota-reviews/ota_auto_mapper_log.txt`

---

## 🛠 Troubleshooting

If a test fails with a "Fatal Error: Class Not Found", ensure you are running the command from the WordPress root directory so `wp-load.php` can be correctly located.

If you encounter a **403 Forbidden** error during synchronization tests, the server IP is likely being blocked by the OTA's bot protection. Use the **Official Content API** or the **Manual Import** feature as a fallback.
