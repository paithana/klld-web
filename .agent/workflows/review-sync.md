---
description: Synchronize tour reviews from OTAs (GYG, Viator) to the WordPress database.
---
// turbo-all
1. Verify credentials for GetYourGuide Integrator API are set in WordPress.
2. Run the sync tool via the CLI wrapper:
```bash
php ota_sync.php
```
3. Alternatively, trigger via URL:
`https://khaolaklanddiscovery.com/ota_sync.php?secret=kld_sync_2024`
4. Check `ota_sync_log.txt` for details of imported reviews.
