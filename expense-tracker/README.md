# Ledger – PHP + MySQL Expense Tracker

## Setup (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\expense-tracker` (Mac/Linux: your htdocs folder).
2. Create `.env` in the project folder using `.env.example` as a template, then enter the host, database name, username, and password from FreeDB.tech.
3. Enable the `pdo_mysql` PHP extension in XAMPP and start **Apache**. For a FreeDB.tech database, use the remote host and port shown in your FreeDB.tech account; do not use `localhost`.
4. In FreeDB.tech's database manager, select your database and import `schema.sql`.
5. Visit http://localhost/expense-tracker/register.php and create an account.

The app reads `.env` automatically. Server environment variables override values in `.env`. Requires PHP 8.0+ with `pdo_mysql`. Charts load Chart.js from a CDN, so an internet connection is needed for them.
