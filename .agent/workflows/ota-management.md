---
description: Manage OTA mappings and use the high-level review management tools.
---
1. Access the **KLLD Tools** menu in the WordPress sidebar.
2. Use **Review Manager** to manually fetch and auto-import reviews with one click.
3. Use **Auto-Mapper** to discover and link new OTA activities to WP Tours:
```bash
wp ota-reviews map
```

### 🎯 OTA Content & Calendar Synchronization

1.  **Content Sync:** Import descriptions, highlights, and itinerary from GYG.
    ```bash
    wp ota-reviews sync-content --post_id=<ID>
    ```
2.  **Calendar Sync:** Import live availability and pricing from GYG.
    ```bash
    wp ota-reviews sync-calendar --post_id=<ID>
    ```

### 🤖 AI Content Optimization (Style: TopRank)

To optimize synced content for SEO and conversion:
1.  Run `wp ota-reviews sync-content --post_id=<ID>` to get the latest raw data.
2.  Use the **Content Writer** skill to rewrite the `post_content` and `tours_highlight` based on Search Intent and E-E-A-T principles.
3.  Manually review and approve the generated content in the WordPress editor.
