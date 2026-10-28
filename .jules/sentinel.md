## 2024-05-16 - Prevent IDOR by Validating User Ownership for Quotes
**Vulnerability:** IDOR (Insecure Direct Object Reference). The quote form allowed an unauthenticated attacker to associate a new quote order with an arbitrary existing user account merely by providing that user's email address.
**Learning:** Relying exclusively on user-provided emails to fetch and bind data to user IDs (`get_user_by('email', $email)`) in public or guest-accessible endpoints creates authorization bypass vectors.
**Prevention:** Before assigning operations to an existing user ID, ensure `is_user_logged_in()` is true and verify that `get_current_user_id()` matches the retrieved user ID. If not, fallback to treating the request as a guest interaction (e.g., ID `0`).
