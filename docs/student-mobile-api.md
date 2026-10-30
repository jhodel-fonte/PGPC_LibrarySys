# Student mobile API

Use HTTPS. All endpoints return JSON. Store tokens in secure device storage.

- `POST /api/v1/student/login`: send `login` (username, email, or school ID), `password`, `device_name` and a Cloudflare `turnstile_token` using action `login`. Returns a bearer `token`, `token_type`, and `expires_at` (30 days). Students who need account setup or approval must first complete it on the website.
- `GET /api/v1/student/dashboard`: send `Authorization: Bearer <token>`. Returns `student` (including the scanner-compatible `qr_payload`), `counts`, `active_loans`, and `recent_returns`. `unpaid_fines` is a decimal string.
- `POST /api/v1/student/logout`: send the bearer token to revoke the current device token; returns 204.

Send `Accept: application/json`. Handle 401 by requesting sign-in, 403 by directing the student to the website for account setup or verification, and 429 by waiting for `retry_after` seconds. The dashboard checks the account's current status on every request.
