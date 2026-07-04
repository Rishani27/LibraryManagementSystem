# University Library Management System (ULMS)

> **Version:** 1.0.0 &nbsp;|&nbsp; **Stack:** PHP · MySQL · HTML · CSS &nbsp;|&nbsp; **Architecture:** Strict Layered Architecture

---

## 📌 Project Overview

The **University Library Management System (ULMS)** is a web-based application designed to automate and manage all operations of a university library within a single branch. The system reduces manual paperwork by providing centralized management of books, users, borrowing, returns, reservations, fines, notifications, and activity logs.

The system supports three user roles — **Admin**, **Librarian**, and **Student** — each with distinct, role-guarded access to relevant features.

---

## 🎯 Vision Statement

> Provide a centralized, efficient library management system that improves circulation workflows, reservation management, overdue tracking, fine administration, and operational reporting to serve a modern university campus.

---

## 📋 Full Software Requirements Specification (SRS)

### 1. Introduction

A web-based system to automate and manage university library operations within a single library. It reduces manual work and manages books, users, loans, reservations, fines, notifications, and activity logs.

### 2. Business Objectives

- Automate library operations
- Maintain organized book catalog
- Improve borrowing and reservations
- Track overdue books and fines
- Provide reporting capabilities
- Maintain security via activity logs
- Reduce paperwork

### 3. Project Scope

**Included:**
- User authentication and role-based access control
- User management (admin)
- Book catalog management (librarian)
- Borrowing system (librarian issues, 14-day loan period)
- Return system (librarian processes returns)
- Reservation system (students reserve, librarians manage)
- Fine management (Rs. 10/day, track paid/unpaid)
- Activity logging (all key actions logged with user + timestamp)
- Notifications for overdue books (dashboard warnings)
- Reports (borrowed, overdue, fine, activity) with print support
- Dashboard statistics per role

**Excluded:**
- Online payments
- Password reset via email
- Multi-library support
- Mobile application
- Email/SMS notifications
- Book renewal system

### 4. Actors / User Classes

| Actor | Responsibilities |
|-------|----------------|
| **Admin** | System management, user creation/deletion, view logs |
| **Librarian** | Library operations: books, borrow, return, reservations, fines, reports |
| **Student** | Browse catalog, view own loans, place/cancel reservations |

### 5. Functional Requirements

| ID | Requirement | Actor |
|----|-------------|-------|
| FR-01 | User authentication via username + password | All |
| FR-02 | Role-based access control (Admin/Librarian/Student) | System |
| FR-03 | User Management: create librarian/student accounts, delete, view registry | Admin |
| FR-04 | Role-specific dashboard statistics | All |
| FR-05 | Book Catalog: add, edit, delete, search, filter books | Librarian |
| FR-06 | Borrowing: issue books, 14-day due date, reduce available copies | Librarian |
| FR-07 | Return: process returns, restore book availability | Librarian |
| FR-08 | Reservation: students reserve unavailable books; librarians manage | Both |
| FR-09 | Fine: Rs. 10/day overdue; track unpaid and paid fines | Librarian |
| FR-10 | Notifications: overdue warnings on dashboards | All |
| FR-11 | Reports: borrowed, overdue, fine, activity; print/date filter | Librarian |
| FR-12 | Activity Logging: login, logout, CRUD actions, all logged with user + timestamp | System |

### 6. Book Fields
- Title, Author, ISBN, Category, Total Copies, Available Copies

### 7. Log Events
Login · Logout · Account Creation · Account Deletion · Book Add/Update/Delete · Borrow · Return · Fine Settlement

### 8. Non-Functional Requirements

| Category | Requirement |
|----------|-------------|
| Performance | Support 1000+ users; dashboard loads < 3 seconds |
| Reliability | All transactions stored in database with ACID guarantees |
| Security | RBAC, bcrypt password hashing, session management, activity logging |
| Usability | Simple, intuitive UI for all three roles |
| Compatibility | Chrome, Firefox, Opera |
| Maintainability | Strict layered architecture, modular PHP classes |
| Scalability | Future feature upgrades supported via modular design |
| Portability | Windows (XAMPP) + Linux (LAMP/LEMP) |

---

## 🏗️ Architecture

The system strictly follows **Layered Architecture** with four distinct layers:

```
┌─────────────────────────────────────────────────────┐
│          PRESENTATION LAYER (UI Only)               │
│  app/presentation/{admin,librarian,student}/*.php   │
│  ✘ NO SQL  ✘ NO business rules                      │
└────────────────────────┬────────────────────────────┘
                         │ calls
┌────────────────────────▼────────────────────────────┐
│          BUSINESS LAYER (Rules & Validation)        │
│  app/business/services/*.php                        │
│  app/business/validators/*.php                      │
│  ✘ NO SQL  ✘ NO HTML output                         │
└────────────────────────┬────────────────────────────┘
                         │ calls
┌────────────────────────▼────────────────────────────┐
│          DATA LAYER (Database Access Only)          │
│  app/data/repositories/*.php                        │
│  app/data/models/*.php                              │
│  ✔ ONLY PDO queries  ✔ DTO objects                  │
└────────────────────────┬────────────────────────────┘
                         │ uses
┌────────────────────────▼────────────────────────────┐
│          CORE LAYER (Infrastructure)                │
│  app/core/Database.php  (PDO Singleton)             │
│  app/core/Session.php   (Auth + Flash)              │
│  app/core/Helper.php    (Utilities)                 │
│  app/config/config.php  (Constants)                 │
└─────────────────────────────────────────────────────┘
```

---

## 📦 Project Structure

```
library-system/
├── public/                      ← Web root (point Apache here)
│   ├── index.php                ← Entry point (redirects by role)
│   ├── login.php                ← Login page + auth handler
│   ├── logout.php               ← Session destroy + redirect
│   └── assets/
│       ├── css/style.css        ← Complete dark-mode design system
│       └── js/script.js         ← Modals, confirmations, print
│
├── app/
│   ├── presentation/            ← PRESENTATION LAYER (UI pages only)
│   │   ├── admin/
│   │   │   ├── dashboard.php   ← Admin dashboard + stats
│   │   │   ├── users.php       ← User management (FR-03)
│   │   │   └── logs.php        ← Activity log viewer (FR-12)
│   │   ├── librarian/
│   │   │   ├── dashboard.php   ← Librarian dashboard + stats
│   │   │   ├── books.php       ← Book catalog CRUD (FR-05)
│   │   │   ├── borrow.php      ← Issue books (FR-06)
│   │   │   ├── return.php      ← Return books (FR-07)
│   │   │   ├── reservations.php← Manage reservations (FR-08)
│   │   │   ├── fines.php       ← Manage fines (FR-09)
│   │   │   └── reports.php     ← All reports (FR-11)
│   │   └── student/
│   │       ├── dashboard.php   ← Student dashboard + alerts
│   │       ├── catalog.php     ← Browse + reserve books
│   │       └── my_reservations.php ← View/cancel reservations
│   │
│   ├── business/                ← BUSINESS LAYER (rules & validation)
│   │   ├── services/
│   │   │   ├── AuthService.php
│   │   │   ├── UserService.php
│   │   │   ├── BookService.php
│   │   │   ├── BorrowService.php
│   │   │   ├── ReturnService.php
│   │   │   ├── ReservationService.php
│   │   │   ├── FineService.php
│   │   │   ├── ReportService.php
│   │   │   └── LogService.php
│   │   └── validators/
│   │       ├── UserValidator.php
│   │       ├── BookValidator.php
│   │       └── BorrowValidator.php
│   │
│   ├── data/                    ← DATA LAYER (PDO queries only)
│   │   ├── repositories/
│   │   │   ├── UserRepository.php
│   │   │   ├── BookRepository.php
│   │   │   ├── LoanRepository.php
│   │   │   ├── ReservationRepository.php
│   │   │   ├── FineRepository.php
│   │   │   └── LogRepository.php
│   │   └── models/
│   │       ├── User.php
│   │       ├── Book.php
│   │       ├── Loan.php
│   │       ├── Reservation.php
│   │       ├── Fine.php
│   │       └── ActivityLog.php
│   │
│   ├── core/                    ← CORE LAYER (infrastructure)
│   │   ├── Database.php         ← PDO singleton
│   │   ├── Session.php          ← Session + RBAC guards
│   │   └── Helper.php           ← Utilities (sanitize, format, etc.)
│   │
│   ├── config/
│   │   └── config.php           ← DB credentials, constants
│   │
│   └── includes/                ← Shared UI partials
│       ├── header.php
│       ├── footer.php
│       ├── nav_admin.php
│       ├── nav_librarian.php
│       └── nav_student.php
│
├── database/
│   └── schema.sql               ← Full MySQL schema with indexes
│
├── setup.php                    ← One-time setup + seed data
├── docs/                        ← Academic documentation
│   ├── uml_diagrams.md
│   ├── sdlc_agile.md
│   ├── test_cases.md
│   ├── user_manual.md
│   ├── deployment_guide.md
│   └── troubleshooting.md
└── README.md                    ← This file
```

---

## ⚙️ Setup Guide (XAMPP — Windows)

### Prerequisites
- XAMPP with PHP 8.1+ and MySQL 8.0+

### Steps

**1. Copy project files**
```
Copy library-system/ to:  C:\xampp\htdocs\library-system\
```

**2. Start XAMPP services**
- Start Apache and MySQL in XAMPP Control Panel

**3. Import database**
```
Open: http://localhost/phpmyadmin
Create database: ulms_db
Import: library-system/database/schema.sql
```

**4. Configure database credentials**
```php
// Edit: app/config/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ulms_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // your MySQL password
```

**5. Run setup script**
```
Visit: http://localhost/library-system/setup.php
This seeds the admin account, sample books, librarian and students.
⚠️ Delete setup.php after use!
```

**6. Access the system**
```
http://localhost/library-system/public/login.php
```

### Demo Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| Librarian | `librarian1` | `lib123` |
| Student | `s001` | `stu123` |

---

## 🐧 Setup Guide (Linux — LAMP)

```bash
# Install LAMP
sudo apt update
sudo apt install apache2 php8.1 php8.1-mysql mysql-server

# Clone/copy project
sudo cp -r library-system /var/www/html/

# Set permissions
sudo chown -R www-data:www-data /var/www/html/library-system
sudo chmod -R 755 /var/www/html/library-system

# Import database
sudo mysql -u root -p < /var/www/html/library-system/database/schema.sql

# Configure DB credentials
sudo nano /var/www/html/library-system/app/config/config.php

# Run setup
http://your-server/library-system/setup.php
```

---

## 👥 Team Distribution (5 Members)

| Member | Feature | Files |
|--------|---------|-------|
| **Member 1** | FR-03 User Management | `UserService.php`, `UserValidator.php`, `UserRepository.php`, `admin/users.php` |
| **Member 2** | FR-05 Book Catalog | `BookService.php`, `BookValidator.php`, `BookRepository.php`, `librarian/books.php` |
| **Member 3** | FR-06 Borrowing + FR-07 Return | `BorrowService.php`, `ReturnService.php`, `BorrowValidator.php`, `LoanRepository.php`, `librarian/borrow.php`, `librarian/return.php` |
| **Member 4** | FR-08 Reservations + FR-09 Fines | `ReservationService.php`, `FineService.php`, `ReservationRepository.php`, `FineRepository.php`, `librarian/reservations.php`, `librarian/fines.php` |
| **Member 5** | FR-11 Reports + FR-12 Activity Logs | `ReportService.php`, `LogService.php`, `LogRepository.php`, `librarian/reports.php`, `admin/logs.php` |

Each member owns the full vertical slice: **Data → Business → Presentation** for their assigned feature.

---

## 🔐 Security Features

- **bcrypt** password hashing (cost 12)
- **Session management** with HTTP-only cookies + SameSite=Lax
- **Session regeneration** on login (prevents session fixation)
- **Role-based access control** (RBAC) enforced per page via `Session::requireLogin()`
- **Input sanitization** with `Helper::e()` (htmlspecialchars) on all output
- **PDO prepared statements** (prevents SQL injection)
- **Activity logging** for all sensitive actions

---

## 📊 Database Schema Summary

| Table | Purpose |
|-------|---------|
| `users` | All user accounts (admin, librarian, student) |
| `books` | Book catalog with copy tracking |
| `loans` | Borrowing records with status tracking |
| `reservations` | Student reservations for unavailable books |
| `fines` | Overdue fines with payment status |
| `activity_logs` | All system events with user + timestamp |

---

## 🚀 Future Enhancements

- Password reset via email (SMTP integration)
- Online fine payment (payment gateway)
- SMS/email notifications (overdue reminders)
- Book renewal system
- Multi-library support
- Mobile-responsive Progressive Web App
- QR code book scanning
- API for third-party integrations

---

## 📝 License

Academic project — University submission. Not licensed for commercial use.
