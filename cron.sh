#!/usr/bin/bash

# Cron clear cache

PROFILE=/home/u451564824/.profile
WP=/home/u451564824/domains/khaolaklanddiscovery.com/public_html
DATE=$(date +"%d-%B-%Y %H:%M")
echo "CRON REWRITE FLUSH: " 
echo ${DATE}

/usr/local/bin/wp litespeed-online init --path=/home/u451564824/domains/khaolaklanddiscovery.com/public_html
/usr/local/bin/wp elementor flush_css --path=/home/u451564824/domains/khaolaklanddiscovery.com/public_html
/usr/local/bin/wp rewrite flush --path=/home/u451564824/domains/khaolaklanddiscovery.com/public_html
/usr/local/bin/wp litespeed-purge all --path=/home/u451564824/domains/khaolaklanddiscovery.com/public_html

# ── Global OTA Review Sync ────────────────────────
echo "GLOBAL OTA SYNC:"
/usr/local/bin/wp ota-reviews sync --limits=20 --path=/home/u451564824/domains/khaolaklanddiscovery.com/public_html

# ── Google (GMB) Filter & Cleanup ─────────────────
echo "GMB FILTER & CLEANUP:"
php -r 'define("KLLD_TOOL_RUN", true); require_once "/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-load.php"; $_POST["action"] = "ota_db_maintenance"; $_POST["job"] = "gmb_filter"; include "/home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-content/plugins/ota-reviews/review_tool.php";'
/usr/local/bin/wp ota-reviews cleanup --path=/home/u451564824/domains/khaolaklanddiscovery.com/public_html

# ── Google Things to Do Feed Push ─────────────────
echo "GTTD SFTP FEED PUSH:"
php /home/u451564824/domains/khaolaklanddiscovery.com/public_html/wp-content/plugins/ota-reviews/gttd_sftp_push.php


