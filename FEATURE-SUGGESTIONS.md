# Feature Suggestions for Widget Visibility with Descendants

Below are proposed new features that would enhance the plugin's functionality, user experience, and market competitiveness.

---

## 1. Scheduling (Date/Time-Based Visibility)

**Priority:** High

Show or hide widgets within a specific date and time range. Useful for seasonal banners, campaign promotions, and time-limited announcements.

**Implementation outline:**
- Add a new `schedule` rule type with `start_date` and `end_date` fields
- Use WordPress `current_time('timestamp')` for timezone-aware comparison
- Add date picker UI in the admin panel (leverage jQuery UI Datepicker already bundled with WP)

**Example use case:** A holiday sale banner widget visible only between Dec 20 and Jan 5.

---

## 2. Tag (post_tag) Support

**Priority:** High

Currently, categories and custom taxonomies are supported but **tags are not**. Tags are one of WordPress's two default taxonomies and widely used.

**Implementation outline:**
- Add a `tag` rule type to `evaluate_rule()` in `class-visibility-frontend.php`
- Use `has_tag()` for single post checks and `is_tag()` for tag archive pages
- Add tag dropdown in admin JS (similar to category dropdown)

---

## 3. Author-Based Visibility

**Priority:** Medium

Show or hide widgets on specific author archive pages. Valuable for multi-author blogs and publications.

**Implementation outline:**
- Add an `author` rule type
- Use `is_author($author_id)` for evaluation
- Populate author dropdown from `get_users(['who' => 'authors'])`

---

## 4. URL Parameter-Based Rules

**Priority:** Medium

Show or hide widgets based on URL query parameters. Useful for marketing campaigns, A/B testing, and personalized landing pages.

**Implementation outline:**
- Add a `url_param` rule type with `param_name` and `param_value` fields
- Evaluate using `$_GET` with proper sanitization (`sanitize_text_field()`)
- Admin UI: two text inputs for parameter name and expected value

**Example use case:** Show a special offer widget only when `?utm_source=newsletter` is present.

---

## 5. Block Widget Editor (Gutenberg) Support

**Priority:** High

WordPress 5.8+ uses a block-based widget editor by default. The current plugin relies on the classic widget UI hooks (`in_widget_form`), which don't apply in the block editor.

**Implementation outline:**
- Register a Gutenberg sidebar plugin using `@wordpress/edit-widgets` or `@wordpress/customize-widgets`
- Create a React-based panel component that mirrors the current visibility UI
- Use `widget_block_content` filter or block attributes for data persistence
- Maintain backward compatibility with the classic widget editor

---

## 6. Rule Presets (Templates)

**Priority:** Medium

Save frequently used rule combinations as reusable templates. Dramatically speeds up configuration for sites with many widgets sharing similar rules.

**Implementation outline:**
- Store presets in `wp_options` table as serialized arrays
- Add "Save as Preset" and "Load Preset" buttons to the visibility panel
- Admin AJAX endpoints for CRUD operations on presets
- Import preset data into widget visibility settings on load

---

## 7. WooCommerce Integration

**Priority:** High (for e-commerce sites)

Conditional visibility for WooCommerce-specific pages: shop, cart, checkout, my account, product categories, and individual products.

**Implementation outline:**
- Detect WooCommerce activation via `class_exists('WooCommerce')`
- Add rule types: `woo_shop`, `woo_cart`, `woo_checkout`, `woo_account`, `woo_product_cat`
- Use WooCommerce conditional functions: `is_shop()`, `is_cart()`, `is_checkout()`, `is_account_page()`, `is_product_category()`
- Only load WooCommerce rules when the plugin is active

---

## 8. Import / Export Visibility Rules

**Priority:** Low

Export all widget visibility rules as a JSON file and import them on another site. Essential for agencies managing multiple similar WordPress installations.

**Implementation outline:**
- Add an admin settings page under Appearance or Tools
- Export: iterate all widget options, extract visibility data, generate JSON
- Import: validate JSON structure, merge or replace existing rules
- Include a dry-run/preview mode before applying imports

---

## 9. Device-Based Visibility (Responsive)

**Priority:** Medium

Show or hide widgets based on device type (mobile, tablet, desktop). Unlike CSS-based hiding (`display: none`), server-side filtering prevents the widget from rendering entirely — better for performance.

**Implementation outline:**
- Add a `device` rule type with values: `mobile`, `tablet`, `desktop`
- Use `wp_is_mobile()` as a baseline; optionally integrate a user-agent parser for tablet detection
- Note limitation: server-side device detection is approximate; document this clearly

---

## 10. Preview / Debug Mode

**Priority:** Medium

An admin-only overlay or panel that shows which visibility rules are active on the current page and why each widget is shown or hidden. Invaluable for debugging complex rule sets.

**Implementation outline:**
- Add a front-end debug bar (visible only to users with `edit_theme_options` capability)
- Hook into `widget_display_callback` to collect evaluation results
- Display results in a fixed admin bar panel or footer overlay
- Include rule-by-rule pass/fail breakdown per widget

---

## Implementation Priority Matrix

| Feature | Impact | Effort | Priority |
| --- | --- | --- | --- |
| Scheduling | High | Low | **P1** |
| Tag Support | High | Low | **P1** |
| Block Editor Support | High | High | **P1** |
| WooCommerce Integration | High | Medium | **P2** |
| Author Visibility | Medium | Low | **P2** |
| URL Parameter Rules | Medium | Low | **P2** |
| Device-Based Visibility | Medium | Medium | **P2** |
| Preview/Debug Mode | Medium | Medium | **P3** |
| Rule Presets | Medium | Medium | **P3** |
| Import/Export | Low | Medium | **P3** |
