## 2026-02-09 - Admin Logic in REST Requests
**Vulnerability:** Widget sanitization logic was wrapped in `if (is_admin())`. This bypassed security checks when widgets were updated via the REST API (e.g., Block Editor), allowing unsanitized data (potential Stored XSS/DoS) to be saved.
**Learning:** `is_admin()` returns false during REST API requests. Security-critical logic like data sanitization must run on all requests, or specifically target the update hooks regardless of context.
**Prevention:** Do not wrap data handling/sanitization logic in `is_admin()`. Use hooks that fire on specific actions (like `widget_update_callback`) instead of relying on the global request context.

## 2026-02-09 - wp_unslash Bypass via JSON Array Input
**Vulnerability:** `save_visibility_settings` checked `is_string($data)` before unslashing and decoding. If an array was passed (e.g., via direct manipulation), it bypassed `wp_unslash` and `json_decode`, passing potentially slashed data directly to storage.
**Learning:** WordPress slashes all input (`$_POST`, etc.). Relying on `is_string()` to trigger unslashing can be bypassed by sending an array. Always assume input can be manipulated.
**Prevention:** Strictly enforce expected data types (e.g., JSON string) and reject unexpected types instead of falling back to default handling that might skip security steps.
