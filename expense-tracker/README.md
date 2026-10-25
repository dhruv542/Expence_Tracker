# Ledger – PHP + MySQL Expense Tracker

## Setup (XAMPP)
1. Copy this folder to `C:\xampp\htdocs\expense-tracker` (Mac/Linux: your htdocs folder).
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open http://localhost/phpmyadmin → **Import** → choose `schema.sql` → Go.
4. If your MySQL has a password, edit `config/db.php`.
5. Visit http://localhost/expense-tracker/register.php and create an account.

Requires PHP 8.0+. Charts load Chart.js from a CDN, so an internet connection is needed for them.
