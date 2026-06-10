# 📚 Library Management System (LMS)
ប្រព័ន្ធគ្រប់គ្រងបណ្ណាល័យ

## 🏗️ Project Structure

```
lms/
├── index.php                  # Redirect to admin login
├── setup.sql                  # Database setup script
├── includes/
│   ├── db.php                 # Database connection
│   ├── auth.php               # Auth helpers & session
│   ├── admin_layout.php       # Admin sidebar/header layout
│   └── member_layout.php      # Member sidebar/header layout
├── admin/
│   ├── login.php              # Admin login
│   ├── logout.php             # Admin logout
│   ├── dashboard.php          # Admin dashboard with stats
│   ├── books.php              # Books CRUD management
│   ├── members.php            # Members CRUD management
│   ├── borrows.php            # Borrow/Return management
│   └── reports.php            # Charts & reports
├── member/
│   ├── login.php              # Member login
│   ├── logout.php             # Member logout
│   ├── dashboard.php          # Member dashboard
│   ├── catalog.php            # Browse book catalog
│   ├── my_borrows.php         # View borrow history
│   └── profile.php            # Edit profile & password
└── uploads/
    └── books/                 # Book cover images
```

## ⚙️ Installation

### Requirements
- PHP 7.4+
- MySQL 5.7+ or MariaDB 10.3+
- Apache/Nginx with mod_rewrite

### Steps

1. **Copy project** to your web root (e.g., `/var/www/html/lms` or `htdocs/lms`)

2. **Create database** by running setup.sql:
   ```sql
   mysql -u root -p < setup.sql
   ```
   Or paste into phpMyAdmin.

3. **Configure DB credentials** in `includes/db.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'library_db');
   ```

4. **Set permissions** for uploads folder:
   ```bash
   chmod 755 uploads/books/
   ```

5. **Access the system**:
   - Admin: `http://localhost/lms/admin/login.php`
   - Member: `http://localhost/lms/member/login.php`

## 🔐 Default Login

| Role   | Username | Password  |
|--------|----------|-----------|
| Admin  | admin    | admin123  |

> ⚠️ Change the default admin password after first login!

## ✨ Features

### Admin Panel
- 📊 Dashboard with live statistics (books, members, borrows, overdue, fines)
- 📚 Books Management — Add/Edit/Delete with image upload, search & category filter
- 👥 Members Management — CRUD with auto-generated member codes
- 📖 Borrow/Return — Issue books, process returns with automatic fine calculation
- 📈 Reports — Charts for monthly borrows, category distribution, top books & members

### Member Portal
- 🏠 Dashboard with active borrows and overdue alerts
- 🔍 Book Catalog — Browse all books with search/filter
- 📋 My Borrows — Full history with status and fines
- 👤 Profile — Edit personal info and change password

## 💰 Fine System
- Default rate: **$0.50/day** for late returns
- Fines auto-calculated on return based on overdue days
- Configurable in `admin/borrows.php` → `$FINE_RATE`

## 🛠️ Tech Stack
- **Frontend**: HTML5, CSS3, Vanilla JavaScript, Font Awesome 6, Chart.js 4
- **Backend**: PHP 7.4+ (PDO/MySQLi)
- **Database**: MySQL / MariaDB
- **Design**: Dark theme with glassmorphism UI

## 📝 Notes
- Passwords are hashed using PHP's `password_hash()` (bcrypt)
- Sessions are used for authentication (separate sessions for admin/member)
- Book availability auto-updates on borrow/return
- Images stored in `uploads/books/` directory
