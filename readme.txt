=== Cybokron Advanced Widget Visibility ===
Contributors: cybokron
Tags: widget, visibility, descendants, grandchildren, pages
Requires at least: 5.2
Tested up to: 6.9
Stable tag: 1.7.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Show or hide widgets by page, category, taxonomy, post type, and user context with descendant support.

== Description ==

Jetpack's Widget Visibility only supports "Include children" which covers direct children (1 level deep). It doesn't include grandchildren, great-grandchildren, or deeper nested pages.

This plugin adds an **"Include all descendants"** option that includes ALL levels of nested pages — grandchildren, great-grandchildren, and beyond.

**Features:**

* Show/Hide widgets based on conditions
* Page visibility with full descendant support
* Category visibility with hierarchy support
* Hierarchical custom taxonomy visibility with descendant support
* Post type conditions
* Special pages: Front page, Blog, Archive, Search, 404
* User role targeting (any selected role)
* User state: Logged in / Logged out
* Multiple conditions with AND/OR logic
* Jetpack-free — no dependencies
* 30 languages included
* Follows WordPress coding standards

**Supported Languages (30):**

Turkish, English, Spanish, German, French, Italian, Portuguese (Brazil), Portuguese (Portugal), Dutch, Polish, Russian, Japanese, Chinese (Simplified), Chinese (Traditional), Korean, Arabic, Hebrew, Swedish, Norwegian, Danish, Finnish, Greek, Czech, Hungarian, Romanian, Ukrainian, Vietnamese, Thai, Indonesian, Hindi, Slovak

== Installation ==

1. Upload the `cybokron-advanced-widget-visibility` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Appearance → Widgets
4. Edit any widget and click the "Visibility" button

== Frequently Asked Questions ==

= Does this replace Jetpack Widget Visibility? =

Yes, this is a standalone alternative. You can use this instead of Jetpack's visibility feature, or alongside it (they work independently).

= Will this slow down my site? =

No. The visibility checks are very lightweight and only run when widgets are being displayed.

= Can I use this with block-based widgets? =

This plugin works with classic widgets. For block-based widget areas, the visibility controls appear in the widget settings.

= What is the difference between "Include children" and "Include all descendants"? =

"Include children" covers direct children only (1 level deep). "Include all descendants" covers ALL nested levels — grandchildren, great-grandchildren, and beyond.

== Screenshots ==

1. Visibility panel with Show/Hide dropdown, condition types, and descendant options
2. Multiple conditions with AND/OR logic support
3. Page hierarchy with descendant matching

== Changelog ==

= 1.7.0 =
* Feature: Added dedicated settings page with dashicons-visibility sidebar icon.
* Feature: Global bypass toggle to temporarily disable all visibility rules for debugging.
* Feature: Configurable maximum rules per widget (1-200, default 50).
* Feature: Option to delete all visibility data when plugin is uninstalled.
* Feature: Colored application icon displayed on settings page header.
* Enhancement: Quick links to Widgets page and GitHub support.
* Changed: Sidebar icon now uses WordPress dashicon instead of base64 PNG.

= 1.6.1 =
* Added: Admin menu page with custom sidebar icon for quick access to plugin info.
* Added: Plugin icon in WordPress admin sidebar.
* Enhancement: About page with getting started guide and feature overview.

= 1.6.0 =
* Rebranded: Plugin renamed to "Cybokron Advanced Widget Visibility".
* Changed: Plugin URI updated to new GitHub repository.
* Changed: All GitHub links updated to new repository name.
* Changed: Translation template and catalog headers updated.

= 1.5.1 =
* Fixed: Text domain aligned to WordPress.org assigned slug `widget-visibility-with-descendants`.
* Fixed: Renamed main plugin file to match WordPress.org slug.
* Fixed: Removed `.distignore` from distribution package.
* Changed: Translation file names updated to match new text domain.

= 1.5.0 =
* WordPress.org submission release.
* Changed: Renamed plugin slug and main file to `cybokron-descendant-visibility-widgets`.
* Changed: Text domain aligned to plugin slug `cybokron-descendant-visibility-widgets`.
* Changed: Contributors updated to valid WordPress.org username `cybokron`.
* Added: `readme.txt` in WordPress.org standard format.
* Added: `.distignore` for clean distribution packaging.
* Added: `uninstall.php` with proper `WP_UNINSTALL_PLUGIN` guard.
* Added: GitHub Actions deploy workflow for WordPress.org SVN.
* Removed: Development artifact `.jules/` directory.

= 1.4.7 =
* Security: Restricted widget visibility UI rendering to users with `edit_theme_options`.
* Security: Prevented visibility data loss by restoring previous `wvd_visibility` settings when unauthorized users trigger widget updates.

= 1.4.6 =
* Docs: Reviewed recent merged PRs and synchronized release notes for maintainers.

= 1.4.5 =
* Fix: Added defensive `is_array()` validation for widget instance payloads.

= 1.4.4 =
* Performance: Eliminated N+1 admin queries in page/category option generation.
* Stability: Prevented duplicate admin panel event handlers.
* Hardening: Strengthened frontend rule validation.

= 1.4.3 =
* Accessibility: Converted visibility panel action controls to semantic button elements.
* Accessibility: Added `aria-label` and keyboard-visible focus styles.

= 1.4.2 =
* Compliance: Removed custom GitHub updater integration for WordPress.org rules.

= 1.4.1 =
* Security: Ensured widget visibility sanitization runs for REST widget updates.
* i18n: Synced all translation catalogs and removed obsolete keys.

= 1.4.0 =
* Feature: Added GitHub updater integration (later removed for WordPress.org compliance).

= 1.3.3 =
* Fix: Removed hidden development artifact and normalized line endings.

= 1.3.2 =
* Fix: Standardized plugin text domain usage across plugin files.

= 1.3.1 =
* Fix: Improved category rule evaluation robustness for single posts.

= 1.3.0 =
* Feature: Added taxonomy rule type for hierarchical custom taxonomies.
* Feature: Added user_role rule type with multi-select role matching.

= 1.1.0 =
* Added 30 language translations.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.7.0 =
New settings page with global bypass, configurable rule limits, and uninstall data cleanup option.

= 1.6.1 =
Added admin menu page with custom sidebar icon.

= 1.6.0 =
Plugin rebranded to "Cybokron Advanced Widget Visibility". No functional changes.

= 1.5.1 =
Text domain and plugin file aligned to WordPress.org assigned slug.

= 1.5.0 =
WordPress.org submission release with aligned plugin slug, text domain, and distribution packaging.
