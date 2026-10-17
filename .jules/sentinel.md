## 2026-10-17 - IDOR in Order Association
**Vulnerability:** Unauthenticated users could assign new orders to existing users accounts by providing their email address in the quote form.
**Learning:** `get_user_by("email", $email)` was used to look up existing customers, and the found user ID was blindly used for the order, without checking if the current user was actually authenticated as that user.
**Prevention:** When assigning sensitive data (like orders) to an existing user account based on unauthenticated input (like an email address), always verify authorization using `is_user_logged_in()` and `get_current_user_id()`. Fall back to guest (0) if unauthorized.
