## 2026-01-30 - WordPress Input Handling
**Vulnerability:** Use of `stripslashes` instead of `wp_unslash` for handling slashed data.
**Learning:** WordPress adds slashes to `$_POST`, `$_GET`, etc. `wp_unslash` is the standard way to remove them, handling both strings and arrays recursively, whereas `stripslashes` only handles strings. Using `wp_unslash` ensures consistency and compatibility with WordPress core behavior.
**Prevention:** Always use `wp_unslash` when processing input data that might have been slashed by WordPress, especially before operations like `json_decode`.
