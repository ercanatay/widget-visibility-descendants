# Widget Visibility with Descendants

**Contributors:** ercanatay
**Tags:** widget, visibility, descendants, grandchildren, pages
**Requires at least:** 5.2
**Tested up to:** 6.9
**Stable tag:** 1.1.0
**Requires PHP:** 7.4
**License:** GPLv2 or later
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Show or hide widgets on pages and all their child pages.

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
- 📝 **Post type** conditions
- 🏠 **Special pages**: Front page, Blog, Archive, Search, 404
- 👤 **User state**: Logged in / Logged out
- 🔗 **Multiple conditions** with AND/OR logic
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
| Post Type | Any post type (post, page, custom) |
| Front Page | Site front page |
| Blog | Blog posts page |
| Archive | Any archive page |
| Search | Search results page |
| 404 | Not found page |
| Single Post | Any single post |
| Logged In | User is logged in |
| Logged Out | User is not logged in |

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
