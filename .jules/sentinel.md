## 2024-10-13 - Added Transient-Based IP Rate Limiting and Status Validation
**Vulnerability:** Missing rate limiting on quote submission endpoint (DoS/Spam risk) and missing status validation for products (IDOR risk).
**Learning:** WordPress transients combined with `md5($ip)` provide an efficient, stateless way to add basic rate-limiting to AJAX endpoints without needing database schema changes. Also, fetching a WooCommerce product by ID without validating its status allows interacting with draft/private products.
**Prevention:** Always validate object visibility (e.g., `$product->get_status() === 'publish'`) before allowing actions on it. Use short-lived transients for endpoint rate limiting.
