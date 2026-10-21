
## 2024-05-24 - Unauthenticated Order Injection (IDOR) via Custom Quote Flow
**Vulnerability:** A custom WooCommerce quote form endpoint allowed unauthenticated users to inject orders into any existing user account if they provided that user's email address in the quote form.
**Learning:** When generating WooCommerce orders from custom unauthenticated endpoints (like a frontend quote form), using `get_user_by( 'email', $email )` to auto-assign the order blindly assumes the user submitting the form owns that email. This is a severe IDOR/Authorization bypass, allowing attackers to spam existing accounts with fake orders and emails.
**Prevention:** Always verify `is_user_logged_in()` and `get_current_user_id() === $customer->ID` before assigning an order to an existing account. If the user is unauthenticated but the email exists, the order must be assigned to guest (ID 0) to prevent cross-account injection.
