=== AccessYes Accessibility Widget for ADA, EAA & WCAG Readiness ===
Contributors: cookieyesdev
Tags: web accessibility, wp accessibility, accessibility widget, wcag, ada
Requires at least: 5.0.0
Tested up to: 6.9
Requires PHP: 5.6
Stable tag: 3.1.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Free WordPress accessibility widget to improve accessibility for your website visitors. Supports efforts towards meeting WCAG, ADA & EAA requirements.

== Description ==

AccessYes Accessibility Widget is a free, lightweight, and user-friendly plugin that adds an accessibility overlay to your WordPress website.

The accessibility widget provides tools to help your site better align with accessibility standards such as the Web Content Accessibility Guidelines (WCAG) 2.1 AA, as well as accessibility laws like the Americans with Disabilities Act (ADA) and the European Accessibility Act (EAA).

With just a few clicks, your website visitors can customise font size, color contrast, spacing, and more to create a browsing experience that fits their accessibility needs.

**Disclaimer:**

AccessYes Accessibility Widget is intended to support your accessibility efforts. It does not guarantee conformance with WCAG or compliance with laws such as the ADA, EAA, or other regulations. Full compliance may still require a comprehensive audit and remediation.


== Why accessibility matters ==

Over 1 billion people globally live with some form of disability. This makes web accessibility essential to ensure your content is usable for everyone, including those with visual, cognitive, motor, and neurological impairments.

With WordPress powering over 43% of all websites in 2026, improving accessibility on WordPress sites can make a big difference in creating a more inclusive web.

AccessYes Accessibility Widget helps you take a step in that direction by giving visitors simple tools to adjust how your WordPress website looks and behaves based on their needs.

== Features ==

= Content adjustments =
* **Font size controls:** Enable users to adjust the font size for enhanced readability.
* **Highlight title:** Emphasise page titles to aid scanning and comprehension.
* **Highlight features:** Highlight all links and/or page titles for easier scanning and focus.
* **Dyslexia-friendly fonts:** Let users switch to high-legibility fonts for better cognitive accessibility.
* **Font weight control:** Bolden text across your site to increase contrast and visibility.
* **Letter spacing & line height adjustments:** Enable precise control over text layout to support users with dyslexia or reading challenges.
* **Align left:** Align all content to the left for a consistent reading experience.

= Color adjustments =
* **Contrast modes:** Includes Dark Mode, Light Mode, and High Contrast for visual comfort.
* **Saturation options:** Allow users to toggle between low saturation, high saturation, and monochrome (grayscale) views.


= Navigation adjustments =
* **Reading guide:** A horizontal guide that helps users track lines of text while reading.
* **Pause animations:** Let users pause animations or motions that could be distracting or trigger discomfort.
* **Big cursor:** Increase cursor size and contrast for better navigation visibility.

= Accessibility statement =
* **Create an accessibility statement:** Generate your website accessibility statement inside the tool and display it in the widget.
* **Link an existing statement:** Connect your existing accessibility statement or VPAT and display it within the widget.


**Need help?** Write to us at support@cookieyes.com with a quick note in the subject line: CookieYes Accessibility Widget

**Got feedback about the plugin?** We’d love to hear it! Drop us a line at accessyes@cookieyes.com.


*Note: AccessYes is a standalone accessibility widget developed by CookieYes. It’s a separate tool designed to work independently from the CookieYes platform.*


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. The Accessibility Widget will appear automatically on your site. No setup required.


== Frequently Asked Questions ==

= Does this plugin help with accessibility law compliance? =
Yes. It is designed to support compliance with WCAG, ADA, EAA, and EN 301 549 standards. However, the AccessYes Accessibility Widget does not ensure full legal compliance with ADA, WCAG, EAA, EN 301 549, or similar standards. A complete audit and remediation process may still be necessary.

= Is user data collected by the plugin? =
No. The plugin does not track or store any user data, ensuring GDPR compliance.

= Can users reset the settings they applied? =
Yes. A "Reset Settings" button is provided to quickly revert all changes.

= Can the plugin be used on multilingual websites? =
Absolutely. The plugin supports over 50 languages and adapts based on the selected language.

== Screenshots ==

1. Accessibility options within the widget.
2. Customise widget color, size, and position.
3. Create or link accessibility statement in widget.
4. Accessibility overlay general settings.

== Changelog ==

= 3.1.3 2026-03-30 =

[Fix] - Resolved missing pointer cursors on various interactive UI elements within the plugin settings dashboard.
[Fix] - Fixed translation issue for the "Reset settings" button in the accessibility widget.
[Add] - Global WordPress admin notice for the review banner to improve visibility.
[Enhancement] - Refactored uninstall feedback modal layout and removed the mandatory option selection requirement.
[Enhancement] - Updated plugin support links to point directly to the WordPress.org support forum.

= 3.1.2 2026-03-13 =

[Add] - Accessibility statement generator.
[Fix] - Ensure 'disableContrast()' removes the class from '<body>' instead of 'documentElement'.
[Enhancement] - Add 'cya11y-dark-contrast' body class when Dark Contrast mode is active to allow site CSS overrides.
[Enhancement] - Add 'nav' to 'DARK_CONTRAST_SELECTORS' to support pagination elements in Dark Contrast mode.

= 3.1.1 2026-02-19 =

[Add] - Implemented the uninstall feedback pop.
[Enhancement] - Removed the tooltip from the widget button and enhanced widget focus visibility.
[Fix] - Minor bugs.

= 3.1.0 2026-02-04 =

[Fix] - The Reading Guide is not functional when color adjustment modes such as High Contrast, High Saturation, Low Saturation, or Monochrome are enabled.

= 3.0.9.1 2026-02-02 =

[Fix] - Font size reset preserves original inline styles.

= 3.0.9 2026-01-13 =

[Fix] - Font size override issue.

= 3.0.8 2025-12-26 =

[Fix] - Incorrect font size issue.

= 3.0.7 2025-12-4 =

[Compatibility] – Tested OK with WordPress version 6.9.
[Add] - Ukrainian language support.
[Fix] - Minor fixes.

= 3.0.6 2025-10-16 =

[Add] - Latvian and Lithuanian language support.
[Fix] - Minor fixes.

= 3.0.5 2025-09-24 =

[Fix] - Removed duplicate entry of slovene language.

= 3.0.4 2025-08-18 =

[Fix] - Language is not loading properly when Norwegian bokmål is selected.
[Enhancement] - Added custom event "cya11y:widgetStateChanged" to track widget changes.

= 3.0.3 2025-07-31 =

[Fix] - Resolved a conflict issue with the MonsterInsights plugin.
[Fix] - Corrected fallback language behavior when translation for the site language is unavailable.
[Fix] - Language files were not loading properly when a caching plugin is active.

= 3.0.2 = 

[Fix] – Added translation to the widget button tooltip

= 3.0.1 = 

[Enhancement] - New improved UI

= 3.0.0 = 

[Compatibility] - Tested OK with WordPress version 6.8
[Enhancement] - Added accessibility widget
[Enhancement] - New improved UI

== Upgrade Notice ==

= 3.1.3 2026-03-30 =

[Fix] - Resolved missing pointer cursors on various interactive UI elements within the plugin settings dashboard.
[Fix] - Fixed translation issue for the "Reset settings" button in the accessibility widget.
[Add] - Global WordPress admin notice for the review banner to improve visibility.
[Enhancement] - Refactored uninstall feedback modal layout and removed the mandatory option selection requirement.
[Enhancement] - Updated plugin support links to point directly to the WordPress.org support forum.

