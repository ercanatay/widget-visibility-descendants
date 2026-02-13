## 2026-02-12 - [Widget Visibility Data Loss Prevention]
**Vulnerability:** Widget visibility settings could be silently deleted if a widget update was triggered by a user without `edit_theme_options` capability (e.g., via a third-party plugin or custom code), because the UI wouldn't render the hidden field, and the save handler would return the new instance without the visibility data.
**Learning:** Checking capabilities in save handlers is insufficient if the handler returns the *new* instance without restoring protected data from the *old* instance. Simply returning early might persist a state where data is missing.
**Prevention:** Always restore protected data from `$old_instance` when rejecting an update due to insufficient permissions or missing data in the new instance.
