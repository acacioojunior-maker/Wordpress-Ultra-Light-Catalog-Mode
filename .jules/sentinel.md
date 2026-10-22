## 2024-10-22 - [Insecure Direct Object Reference (IDOR) on Customer Creation]
**Vulnerability:** The quote submission endpoint blindly attached new orders to existing WooCommerce customers if an unauthenticated user submitted a matching email address.
**Learning:** This architectural gap allows an attacker to attach arbitrary quotes to an administrator's or another user's account by providing their email in the quote form, potentially leading to information disclosure or account cluttering.
**Prevention:** Always verify authentication state (`is_user_logged_in()` and `wp_get_current_user()->user_email`) before associating data with an existing user account in public endpoints. Return 0 for guest orders if unauthenticated.
