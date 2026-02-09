# Widget Visibility with Descendants

**Contributors:** ercanatay
**Tags:** widget, visibility, descendants, grandchildren, pages
**Requires at least:** 5.2
**Tested up to:** 6.9
**Stable tag:** 1.4.0
**Requires PHP:** 7.4
**License:** GPLv2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Show or hide widgets by page, category, taxonomy, post type, and user context with descendant support.

== Description ==

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![WordPress](https://img.shields.io/badge/WordPress-5.2%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)

## 🎯 The Problem

Jetpack's Widget Visibility only supports "Include children" which covers direct children (1 level deep). It doesn't include grandchildren, great-grandchildren, or deeper nested pages.

**Example:**
```
/services/                          ← Parent
/services/web-design/               ← Child (Jetpack ✓)
/services/web-design/pricing/       ← Grandchild (Jetpack ✗)
/services/web-design/pricing/faq/   ← Great-grandchild (Jetpack ✗)
```

## ✅ The Solution

This plugin adds an **"Include all descendants"** option that includes ALL levels of nested pages - grandchildren, great-grandchildren, and beyond.

## Features

- 🎛️ **Show/Hide widgets** based on conditions
- 📄 **Page visibility** with full descendant support
- 📁 **Category visibility** with hierarchy support
- 🏷️ **Hierarchical custom taxonomy visibility** with descendant support
- 📝 **Post type** conditions
- 🏠 **Special pages**: Front page, Blog, Archive, Search, 404
- 👥 **User role targeting** (any selected role)
- 👤 **User state**: Logged in / Logged out
- 🔗 **Multiple conditions** with AND/OR logic
- 🔄 **GitHub auto updates** with stable/beta channels
- 🚀 **Jetpack-free** - no dependencies
- 🌍 **30 languages included**
- 🔒 **Secure** - follows WordPress coding standards

## Installation

### From GitHub

1. Download the latest release from [Releases](https://github.com/ercanatay/widget-visibility-descendants/releases)
2. Upload to `/wp-content/plugins/`
3. Activate the plugin in WordPress Admin → Plugins

### Using Git

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/ercanatay/widget-visibility-descendants.git
```

### Manual Installation

1. Download the ZIP file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file and activate

## Usage

### Basic Usage

1. Go to **Appearance → Widgets**
2. Edit any widget
3. Click the **"Visibility"** button
4. Choose **Show** or **Hide**
5. Select condition type (Page, Category, etc.)
6. Select the specific item
7. Check **"Include all descendants"** for nested pages
8. Click **Done** and save the widget

### Include Children vs Include All Descendants

| Option | Covers | Example |
|--------|--------|---------|
| Include children | Direct children only (1 level) | `/parent/child/` ✓ |
| **Include all descendants** | All nested levels (unlimited) | `/parent/child/grandchild/great/` ✓ |

### Multiple Conditions

- Add multiple rules with the **+** button
- Check **"Match all conditions"** for AND logic (all rules must match)
- Leave unchecked for OR logic (any rule can match)

### Condition Types

| Type | Description |
|------|-------------|
| Page | Specific page with optional descendants |
| Category | Category archive or posts in category |
| Taxonomy | Hierarchical public custom taxonomy archive/posts with optional descendants |
| Post Type | Any post type (post, page, custom) |
| User Role | Match logged-in users by one or more selected roles |
| Front Page | Site front page |
| Blog | Blog posts page |
| Archive | Any archive page |
| Search | Search results page |
| 404 | Not found page |
| Single Post | Any single post |
| Logged In | User is logged in |
| Logged Out | User is not logged in |

### GitHub Auto Update Channel

1. Go to **Settings → Widget Visibility Updates**
2. Enable **GitHub updates**
3. Choose update channel:
   - **Stable (recommended):** only non-prerelease GitHub releases
   - **Beta:** includes prerelease GitHub releases
4. Save changes

The plugin then uses WordPress native update checks to detect and install newer GitHub versions.

## Screenshots

### Visibility Panel
The visibility panel appears below each widget with:
- Show/Hide dropdown
- Condition type selector
- Value selector (pages, categories, etc.)
- "Include children" checkbox
- **"Include all descendants"** checkbox (the key feature!)
- "Match all conditions" for AND/OR logic

## Requirements

- WordPress 5.2 or higher
- PHP 7.4 or higher

## Supported Languages

🇹🇷 Turkish, 🇺🇸 English, 🇪🇸 Spanish, 🇩🇪 German, 🇫🇷 French, 🇮🇹 Italian, 🇧🇷 Portuguese (Brazil), 🇵🇹 Portuguese (Portugal), 🇳🇱 Dutch, 🇵🇱 Polish, 🇷🇺 Russian, 🇯🇵 Japanese, 🇨🇳 Chinese (Simplified), 🇹🇼 Chinese (Traditional), 🇰🇷 Korean, 🇸🇦 Arabic, 🇮🇱 Hebrew, 🇸🇪 Swedish, 🇳🇴 Norwegian, 🇩🇰 Danish, 🇫🇮 Finnish, 🇬🇷 Greek, 🇨🇿 Czech, 🇭🇺 Hungarian, 🇷🇴 Romanian, 🇺🇦 Ukrainian, 🇻🇳 Vietnamese, 🇹🇭 Thai, 🇮🇩 Indonesian, 🇮🇳 Hindi, 🇸🇰 Slovak

## Frequently Asked Questions

### Does this replace Jetpack Widget Visibility?

Yes, this is a standalone alternative. You can use this instead of Jetpack's visibility feature, or alongside it (they work independently).

### Will this slow down my site?

No. The visibility checks are very lightweight and only run when widgets are being displayed.

### Can I use this with block-based widgets?

This plugin works with classic widgets. For block-based widget areas, the visibility controls appear in the widget settings.

## Changelog

### 1.4.0 (2026-02-09)
- Feature: Added GitHub updater integration using WordPress native plugin update flow.
- Feature: Added settings page at **Settings → Widget Visibility Updates**.
- Feature: Added update channel control (`stable` / `beta`) with default `stable`.
- Enhancement: Added plugin row **Update Settings** quick link in Plugins screen.
- Enhancement: Added package source normalization during upgrade to ensure correct plugin folder replacement.
- Maintenance: Added updater cache cleanup after settings changes and successful plugin upgrades.

### 1.3.3 (2026-02-08)
- Fix: Removed hidden tracked development artifact at `.jules/sentinel.md` to avoid hidden-file check warnings.
- Fix: Normalized mixed line endings to LF in `README.md` and `assets/css/admin.css`.
- i18n: Renamed translation files in `languages/` to `widget-visibility-descendants-main-*` so file names align with the active text domain.


### 1.3.2 (2026-02-08)
- Fix: Standardized plugin text domain usage to `widget-visibility-descendants-main` across plugin headers and admin UI translation calls.
- Fix: Normalized `grandchildren-visibility.php` to LF line endings to prevent mixed EOL warnings.
- Fix: Removed root `.gitignore` from distributable plugin files to satisfy hidden-file checks.

### 1.3.1 (2026-02-08)
- Fix: Improved category rule evaluation robustness for single posts ([PR #19](https://github.com/ercanatay/widget-visibility-descendants/pull/19)).
- Stability: Switched post ID lookup to `get_queried_object_id()` in widget visibility checks.
- Hardening: Added zero-ID guard and `WP_Error`/array validation for `wp_get_post_categories()` results.
- Type Safety: Cast single-post category IDs to integers for reliable strict comparison.

### 1.3.0 (2026-02-08)
- Feature: Added `taxonomy` rule type for hierarchical custom taxonomies (archive + singular post term matching).
- Feature: Added descendant and child matching support for custom taxonomy terms.
- Feature: Added `user_role` rule type with multi-select role matching (any selected role).
- Security: Added strict sanitization/validation for taxonomy slugs, term IDs, post types, and role arrays.
- Performance: Added request-level taxonomy ancestor caching in frontend evaluator.
- i18n: Added new admin UI strings and updated translation template (.pot) for taxonomy/user role controls.

### 1.1.4 (2026-02-08)
- Security: Added defensive checks to category visibility evaluation to avoid invalid object property access.
- Stability: Validate `get_queried_object()` returns a `WP_Term` before reading term properties on category archives.
- Stability: Validate `get_category()` results and guard against `WP_Error` before reading parent term data on single posts.
- Hardening: Prevent potential PHP warnings/errors and reduce information leakage risk in edge-case category queries.

### 1.1.3 (2026-02-07)
- Fix: Category visibility type safety — cast `get_ancestors()` results to int for reliable strict comparison
- i18n: Complete internationalization of admin JS by replacing hardcoded English labels with `wvdData.i18n.*` references
- i18n: Add missing rule type translation keys (Front Page, Blog, Archive, Search, 404, Single Post, Logged In, Logged Out, Select a post type, Configured)
- Security: Remove unimplemented `taxonomy` and `author` from allowed rule types whitelist
- Updated translation template (.pot) with new strings

### 1.1.2 (2025-01-27)
- Security: Replaced json_encode with wp_json_encode for better WordPress compatibility
- Security: Added capability check (edit_theme_options) before saving widget settings
- Defense-in-depth protection for widget update callback

### 1.1.1 (2025-01-27)
- Security: Added XSS sanitization for page/category/post type titles
- Security: Added DoS protection with rule type whitelist
- Security: Limited maximum rules to 50 to prevent resource exhaustion
- Security: Limited value length to 100 characters to prevent database bloat

### 1.1.0 (2025-01-27)
- Added 30 language translations
- Turkish, Spanish, German, French, Italian, Portuguese, Dutch, Polish, Russian, Japanese, Chinese, Korean, Arabic, Hebrew, Swedish, Norwegian, Danish, Finnish, Greek, Czech, Hungarian, Romanian, Ukrainian, Vietnamese, Thai, Indonesian, Hindi, Slovak

### 1.0.1 (2025-01-27)
- Fixed descendant detection bug (ancestor ID type conversion issue)
- Improved reliability for deeply nested page hierarchies

### 1.0.0 (2025-01-27)
- Initial release
- Page visibility with full descendant support
- Category visibility with hierarchy support
- Post type, special pages, and user state conditions
- Multiple conditions with AND/OR logic

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Author

**Ercan ATAY**
- Website: [ercanatay.com](https://www.ercanatay.com/en/)
- GitHub: [@ercanatay](https://github.com/ercanatay)

## Support

If you encounter any issues or have questions:
- Open an issue on [GitHub Issues](https://github.com/ercanatay/widget-visibility-descendants/issues)
- Check existing issues for solutions

---

**Note:** This plugin is a standalone solution and does not require Jetpack. If you have Jetpack installed, both visibility systems will work independently.
