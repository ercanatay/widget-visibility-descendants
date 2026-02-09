## 2026-02-09 - Admin Logic in REST Requests
**Vulnerability:** Widget sanitization logic was wrapped in `if (is_admin())`. This bypassed security checks when widgets were updated via the REST API (e.g., Block Editor), allowing unsanitized data (potential Stored XSS/DoS) to be saved.
**Learning:** `is_admin()` returns false during REST API requests. Security-critical logic like data sanitization must run on all requests, or specifically target the update hooks regardless of context.
**Prevention:** Do not wrap data handling/sanitization logic in `is_admin()`. Use hooks that fire on specific actions (like `widget_update_callback`) instead of relying on the global request context.

## 2026-02-09 - GitHub API Failure Caching
**Vulnerability:** Uncached API failures led to potential DoS if the GitHub API was down or rate-limited, as every admin page load would retry the request.
**Learning:** Negative caching is critical for external dependencies to prevent cascading failures and resource exhaustion.
**Prevention:** Implement backoff caching (short TTL) for failed or empty API responses.
