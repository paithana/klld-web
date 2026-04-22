#!/usr/bin/bash

PROFILE=/home/u451564824/.profile
WP=/home/u451564824/domains/khaolaklanddiscovery.com/public_html
echo "Elementor Pro Activate: "+$(date +"%d-%B-%Y %H:%M")""

/usr/local/bin/wp elementor-pro license activate ep-M84nEb0zNVzznj8c4HQI1703289756sV7O374LYPCW --path=/home/u451564824/domains/khaolaklanddiscovery.com/public_html --url=https://khaolaklanddiscovery.com
