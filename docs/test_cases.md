# ULMS — Test Cases (30+ Cases)

## Test Plan Overview

| Attribute | Value |
|-----------|-------|
| Testing Types | Functional, Boundary, Negative, Integration |
| Environment | XAMPP, PHP 8.1, MySQL 8.0, Chrome/Firefox |
| Test Data | Setup via setup.php (admin, librarian1, s001–s003) |

---

## FR-01: Authentication Tests

| TC | Test Case | Input | Expected Result | Pass? |
|----|-----------|-------|-----------------|-------|
| TC-01 | Valid admin login | username=admin, password=admin123 | Redirect to admin dashboard | ✅ |
| TC-02 | Valid librarian login | username=librarian1, password=lib123 | Redirect to librarian dashboard | ✅ |
| TC-03 | Valid student login | username=s001, password=stu123 | Redirect to student dashboard | ✅ |
| TC-04 | Wrong password | username=admin, password=wrong | Error: "Invalid username or password" | ✅ |
| TC-05 | Non-existent user | username=nobody, password=x | Error: "Invalid username or password" | ✅ |
| TC-06 | Empty username | username="", password=admin123 | Error: "Username and password are required" | ✅ |
| TC-07 | Access admin page without login | Direct URL to admin/dashboard.php | Redirect to login | ✅ |
| TC-08 | Student accesses admin page | Logged in as student, visit admin URL | Redirect to login with "unauthorized" | ✅ |

---

## FR-03: User Management Tests

| TC | Test Case | Input | Expected Result | Pass? |
|----|-----------|-------|-----------------|-------|
| TC-09 | Create librarian account | Valid name/username/email/password/role=librarian | Success: user created, appears in registry | ✅ |
| TC-10 | Create student account | Valid student data | Success: student appears in registry | ✅ |
| TC-11 | Duplicate username | username already exists | Error: "Username already exists" | ✅ |
| TC-12 | Duplicate email | email already registered | Error: "Email already registered" | ✅ |
| TC-13 | Invalid email format | email="notanemail" | Error: "Valid email required" | ✅ |
| TC-14 | Password too short | password="12" | Error: "Password must be at least 6 characters" | ✅ |
| TC-15 | Delete non-admin user | Delete librarian1 | Success: user removed from registry | ✅ |
| TC-16 | Attempt self-deletion | Admin deletes own account | Error: "Cannot delete your own account" | ✅ |
| TC-17 | Search user by name | query="Alice" | Returns Alice Johnson in results | ✅ |
| TC-18 | Filter users by role | role=student | Shows only students | ✅ |

---

## FR-05: Book Catalog Tests

| TC | Test Case | Input | Expected Result | Pass? |
|----|-----------|-------|-----------------|-------|
| TC-19 | Add valid book | All required fields filled | Book added, appears in catalog | ✅ |
| TC-20 | Add book without title | title="" | Error: "Book title is required" | ✅ |
| TC-21 | Add book with invalid ISBN | isbn="NOTANISBN!" | Error: "ISBN must be 10–17 digits" | ✅ |
| TC-22 | Add book with 0 copies | total_copies=0 | Error: "Total copies must be at least 1" | ✅ |
| TC-23 | Edit book title | Change title of existing book | Success: title updated in catalog | ✅ |
| TC-24 | Delete book | Delete "Clean Code" | Book removed from catalog | ✅ |
| TC-25 | Search by title | q="Algorithm" | Returns matching books | ✅ |
| TC-26 | Filter by category | category="Database" | Shows only Database books | ✅ |

---

## FR-06/07: Borrow & Return Tests

| TC | Test Case | Input | Expected Result | Pass? |
|----|-----------|-------|-----------------|-------|
| TC-27 | Issue available book | book_id=1, student_id=s001 | Loan created; available copies -1; due=+14 days | ✅ |
| TC-28 | Issue unavailable book | book with 0 available copies | Error: "No available copies" | ✅ |
| TC-29 | Issue same book twice | Student already has this book | Error: "Student already has this book checked out" | ✅ |
| TC-30 | Return on-time book | Return loan before due date | Status=returned; available copies +1; no fine | ✅ |
| TC-31 | Return overdue book (3 days) | Return 3 days after due date | Fine Rs. 30 created; status=returned | ✅ |
| TC-32 | Return already-returned book | Loan status=returned | Error: "Book already returned" | ✅ |

---

## FR-08/09: Reservation & Fine Tests

| TC | Test Case | Input | Expected Result | Pass? |
|----|-----------|-------|-----------------|-------|
| TC-33 | Reserve unavailable book | Student reserves book with 0 copies | Reservation created (pending) | ✅ |
| TC-34 | Reserve available book | Student tries to reserve book in stock | Error: "Book is available — borrow directly" | ✅ |
| TC-35 | Duplicate reservation | Student reserves same book twice | Error: "Already have pending reservation" | ✅ |
| TC-36 | Librarian fulfils reservation | Fulfil pending reservation | Status changes to fulfilled | ✅ |
| TC-37 | Cancel reservation | Cancel pending reservation | Status changes to cancelled | ✅ |
| TC-38 | Settle fine | Librarian settles Rs. 30 fine | Fine status=paid; settled_at set | ✅ |
| TC-39 | Settle already-paid fine | Fine status already=paid | Error: "Fine already paid" | ✅ |
| TC-40 | Process overdue fines | Trigger batch processing | New fine records created for all overdue loans | ✅ |

---

## FR-11: Reports Tests

| TC | Test Case | Input | Expected Result | Pass? |
|----|-----------|-------|-----------------|-------|
| TC-41 | Borrowed report (no filter) | type=borrowed | Shows all loan records | ✅ |
| TC-42 | Borrowed report (date range) | from=2026-01-01, to=2026-01-31 | Shows only Jan 2026 loans | ✅ |
| TC-43 | Overdue report | type=overdue | Shows all active overdue loans | ✅ |
| TC-44 | Fine report | type=fines, date range | Shows fines in date range | ✅ |
| TC-45 | Activity report | type=activity | Shows all log entries | ✅ |
| TC-46 | Print report | Click print button | Sidebar and buttons hidden in print | ✅ |

---

## FR-12: Activity Logging Tests

| TC | Test Case | Expected Log Entry | Pass? |
|----|-----------|-------------------|-------|
| TC-47 | Login | action=LOGIN, username=admin | ✅ |
| TC-48 | Logout | action=LOGOUT, username=admin | ✅ |
| TC-49 | Create user | action=USER_CREATED, description includes new username | ✅ |
| TC-50 | Delete user | action=USER_DELETED, description includes deleted username | ✅ |
| TC-51 | Add book | action=BOOK_ADDED, description includes book title | ✅ |
| TC-52 | Issue book | action=BOOK_BORROWED, description includes student name + due date | ✅ |
| TC-53 | Return book | action=BOOK_RETURNED, description includes fine amount | ✅ |
| TC-54 | Settle fine | action=FINE_SETTLED, description includes amount | ✅ |

---

## Test Summary

| Category | Total | Expected Pass |
|----------|-------|---------------|
| Authentication | 8 | 8 |
| User Management | 10 | 10 |
| Book Catalog | 8 | 8 |
| Borrow & Return | 6 | 6 |
| Reservation & Fines | 8 | 8 |
| Reports | 6 | 6 |
| Activity Logging | 8 | 8 |
| **Total** | **54** | **54** |
