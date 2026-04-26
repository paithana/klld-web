#!/usr/bin/bash

# Cron clear cache
WP=/home/u451564824/domains/khaolaklanddiscovery.com/public_html
DATE=$(date +"%d-%B-%Y %H:%M")
echo "CRON CACHE FLUSH: ${DATE}"

/usr/local/bin/wp litespeed-online init --path=$WP
/usr/local/bin/wp elementor flush_css --path=$WP
/usr/local/bin/wp rewrite flush --path=$WP
/usr/local/bin/wp litespeed-purge all --path=$WP
