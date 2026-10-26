## 2023-10-26 - [Unauthenticated Endpoint Abuse]
**Vulnerability:** The AJAX endpoint `confiar_submit_quote` was unauthenticated and allowed public users to submit form data that resulted in the backend creating WordPress users and sending emails, without any rate limiting.
**Learning:** This is an architectural security gap. Unauthenticated endpoints that create resources (users, emails, orders) can be easily abused by bots for spam, DoS, and resource exhaustion, even if the inputs are sanitized.
**Prevention:** Always implement IP-based rate limiting (e.g., using WordPress transients) on unauthenticated form submissions or API endpoints that perform resource-intensive actions or create database records/send emails.
