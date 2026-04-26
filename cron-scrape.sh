#!/usr/bin/bash

# Cron Review Scraping & Sync
WP=/home/u451564824/domains/khaolaklanddiscovery.com/public_html
DATE=$(date +"%d-%B-%Y %H:%M")
echo "CRON REVIEW SCRAPE: ${DATE}"

# ── Global OTA Review Sync ────────────────────────
echo "GLOBAL OTA SYNC:"
/usr/local/bin/wp ota-reviews sync --limits=20 --path=$WP

# ── Google (GMB) Filter & Cleanup ─────────────────
echo "GMB FILTER & CLEANUP:"
php -r 'define("KLLD_TOOL_RUN", true); require_once "/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-load.php"; $_POST["action"] = "ota_db_maintenance"; $_POST["job"] = "gmb_filter"; include "/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-content/plugins/ota-reviews/review_tool.php";'
/usr/local/bin/wp ota-reviews cleanup --path=$WP

# ── Google Things to Do Feed Push ─────────────────
echo "GTTD SFTP FEED PUSH:"
php $WP/wp-content/plugins/ota-reviews/gttd_sftp_push.php
