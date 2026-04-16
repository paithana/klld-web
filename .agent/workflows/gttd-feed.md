---
description: Generate and push the Google Things to Do product feed via SFTP.
---
// turbo-all
1. Generate the feed and push to Google's server:
```bash
php wp-content/themes/traveler-childtheme/inc/ota-tools/gttd_sftp_push.php
```
2. Verify output in the SFTP log or check the live feed URL:
`https://khaolaklanddiscovery.com/google-tours-feed.php?format=xml`
3. Access the visual preview for debugging:
`https://khaolaklanddiscovery.com/google-tours-feed.php?preview=1`
