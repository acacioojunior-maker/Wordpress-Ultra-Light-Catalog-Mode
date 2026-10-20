## 2025-02-23 - [IDOR / Authorization Bypass in Order Handler]
**Vulnerability:** Any unauthenticated user could create a quote order and attach it to an existing user account simply by providing their email address during the checkout/quote request process.
**Learning:** `get_user_by( "email", $email )` returns a user object if the email exists. The previous code directly returned this user ID and assigned the order to it without checking if the requester was authenticated as that user.
**Prevention:** Always verify `is_user_logged_in()` and `get_current_user_id() === $customer->ID` before acting on behalf of an existing account or attaching data to it. If the check fails, default to a guest workflow (e.g., return `0`).
