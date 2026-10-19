## 2024-05-24 - [IDOR / Account Hijacking via WooCommerce Order Association]
**Vulnerability:** Unauthenticated users could associate their requested quotes (orders) with any existing customer's account by simply knowing and providing their email address during the quote request.
**Learning:** In custom checkout or quote request flows, querying `get_user_by( 'email', $email )` and attaching the order to that user ID without verifying authentication state allows unauthorized users to manipulate data belonging to other accounts.
**Prevention:** Always verify `is_user_logged_in()` before associating new records with an existing user account. If unauthenticated, but the email exists, fallback to a Guest context (user ID `0`) instead of returning the existing user's ID.
