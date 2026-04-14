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
