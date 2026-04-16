#!/usr/bin/bash

# KLLD OTA Automation Cron Script
# Manages Daily Review Sync and Google Things to Do Feed Delivery

# Configuration
WP_PATH="/home/u451564824/domains/khaolaklanddiscovery.com/public_html"
SYNC_SCRIPT="${WP_PATH}/ota_sync.php"
PICK_SCRIPT="${WP_PATH}/wp-content/themes/traveler-childtheme/inc/ota-tools/gttd_sftp_push.php"
DATE=$(date +"%Y-%m-%d %H:%M:%S")

echo "--------------------------------------------------"
echo "🚀 KLLD Automation Started: ${DATE}"
echo "--------------------------------------------------"

# 1. Sync OTA Reviews
echo "[1/2] Syncing OTA Reviews..."
php ${SYNC_SCRIPT} secret=kld_sync_2024

# 2. Push GTTD Feed to Google
echo "[2/2] Pushing GTTD Feed via SFTP..."
php ${PICK_SCRIPT}

echo "--------------------------------------------------"
echo "✅ KLLD Automation Finished: $(date +'%Y-%m-%d %H:%M:%S')"
echo "--------------------------------------------------"
