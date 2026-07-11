---
name: Admin user setup
description: Admin credentials and DB setup notes for this app
---

# Admin user setup

**Credentials:** `admin@sistema.com` / `admin123`

**Why:** The original INSERT in replit.md used hash `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4...` which is the default Laravel/PHP test hash for "password", NOT "admin123". The DB was updated via PHP script to set the correct hash.

**How to apply:** If resetting the DB, generate a fresh hash: `password_hash('admin123', PASSWORD_BCRYPT)` and use that in the INSERT. Never use the hardcoded hash from the original schema docs.

**Login form field:** The login form uses `name="password"` (not `name="senha"`). The PHP reads `$_POST['password']`.
