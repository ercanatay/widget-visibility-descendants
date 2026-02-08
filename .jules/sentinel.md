# Sentinel Journal

## 2025-05-15 - Strict Input Sanitization
**Vulnerability:** Use of `stripslashes` instead of `wp_unslash`.
**Learning:** `wp_unslash` is context-aware and handles array recursion correctly, whereas `stripslashes` does not. WP standards mandate `wp_unslash` for input handling.
**Prevention:** Always use `wp_unslash` when processing `$_POST`/`$_GET` data.

## 2025-05-15 - Secure HTML Escaping in JS
**Vulnerability:** DOM-based HTML escaping (`div.textContent = text; return div.innerHTML`) is insufficient for attribute contexts because it does not escape quotes.
**Learning:** Always use regex-based replacement to escape all special characters (`&`, `<`, `>`, `"`, `'`) when handling untrusted data in JavaScript, especially for attribute values.
**Prevention:** Use a robust `escapeHtml` function and apply it to all dynamic data, including localization strings (`i18n`), before concatenating into HTML.

## 2025-05-15 - Strict Type Validation
**Vulnerability:** Passing non-string types (arrays) to `sanitize_key` or `sanitize_text_field` triggers PHP warnings and potential type juggling issues.
**Learning:** Always validate input type (`is_string`) before sanitization, especially for complex structures like JSON decoded data.
**Prevention:** Add explicit `is_string()` checks before processing user input in `sanitize_visibility_data` or similar functions.

## 2025-05-15 - Strict Type Safety for Ancestors
**Vulnerability:** Potential logic failure in visibility checks if `get_ancestors()` returns strings instead of integers, causing `in_array(..., true)` to fail.
**Learning:** Even if WordPress functions typically return integers, plugins or filters might alter them. Strict type checking requires explicit casting to ensure robustness.
**Prevention:** Always use `array_map('intval', ...)` on ID arrays retrieved from external sources (like `get_ancestors`) before performing strict comparison checks.

## 2026-02-07 - Defensive Object Checks for WP Functions
**Vulnerability:** Potential PHP warnings/fatal errors and information leakage when `get_queried_object()` returns null or `get_category()` returns `WP_Error`.
**Learning:** WordPress core functions often return multiple types (object/null/error) depending on context. Assuming an object return type is risky, especially when interacting with external plugins or during deletions.
**Prevention:** Always verify object type (`instanceof WP_Term` or `!is_wp_error()`) before accessing properties on return values from WP core functions.
