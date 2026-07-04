# ULMS — User Manual

## Introduction

This manual covers how each user type interacts with the University Library Management System (ULMS). The system is accessed via a web browser at:

```
http://localhost/library-system/public/login.php
```

---

## Part 1: Student Guide

### 1.1 Logging In

1. Open the system URL in your browser
2. Enter your **username** and **password**
3. Click **Sign In**
4. You will be redirected to your Student Dashboard

### 1.2 Student Dashboard

The dashboard shows:
- **Active Loans** — books currently borrowed
- **Overdue** — books past their due date (⚠️ return immediately)
- **Active Reservations** — books you have reserved
- **Unpaid Fines** — fines requiring payment (contact librarian)
- **Overdue alert banner** — if any book is overdue

### 1.3 Browsing the Book Catalog

1. Click **Browse Catalog** in the sidebar
2. View all available books
3. Use the **search bar** to search by title, author, or ISBN
4. Use the **category dropdown** to filter by subject
5. The **Available copies** column shows availability

### 1.4 Placing a Reservation

You can only reserve books that are currently **unavailable** (0 copies):

1. Find a book with 0 available copies in the catalog
2. Click the **🔖 Reserve** button next to it
3. You will see a success message: "Reservation placed for [book title]"
4. The reservation appears in **My Reservations**
5. Wait for the librarian to notify you when the book is available

**Note:** You can only place one pending reservation per book.

### 1.5 Managing Reservations

1. Click **My Reservations** in the sidebar
2. View all your reservations and their status:
   - **Pending** — waiting for book to become available
   - **Fulfilled** — librarian confirmed the book is ready
   - **Cancelled** — reservation was cancelled
3. To cancel a pending reservation, click **✕ Cancel**

### 1.6 Understanding Fines

- Fines are charged at **Rs. 10 per overdue day**
- Your dashboard shows unpaid fines
- Contact your librarian to settle fines in person
- You cannot settle fines through this portal

---

## Part 2: Librarian Guide

### 2.1 Librarian Dashboard

Shows:
- Active Loans count
- Overdue Books count (with list below)
- Pending Reservations
- Unpaid Fines
- Total Fines Settled
- Quick Action buttons

### 2.2 Book Catalog Management

**Add a Book:**
1. Go to **Book Catalog** → Click **+ Add Book**
2. Fill in: Title, Author, Category, Total Copies (ISBN optional)
3. Click **✅ Add Book**
4. The book appears in the catalog immediately

**Edit a Book:**
1. Find the book in the catalog
2. Click **✏️ Edit** → modify fields → click **💾 Save Changes**

**Delete a Book:**
1. Find the book → Click **🗑 Delete**
2. Confirm the deletion dialog

**Search / Filter:**
- Use the search bar to find by title, author, or ISBN
- Use the category dropdown to filter by subject

### 2.3 Issuing a Book (Borrowing)

1. Go to **Issue Books** in the sidebar
2. Select the **Book** from the dropdown (only available books shown)
3. Select the **Student** from the dropdown
4. Click **✅ Issue Book**
5. Due date is automatically set to **14 days** from today
6. The available copies count is immediately reduced

### 2.4 Processing a Return

1. Go to **Return Books** in the sidebar
2. Find the student's loan in the table
3. Review the **Days Overdue** and **Estimated Fine** columns
4. Click **📥 Return** → confirm the dialog
5. If overdue, a fine is automatically created
6. Available copies are restored

### 2.5 Managing Reservations

1. Go to **Reservations** in the sidebar
2. View all reservations (filter: All or Pending Only)
3. **Fulfil:** Click ✅ Fulfil when the book is available for pickup
4. **Cancel:** Click ✕ Cancel to cancel a reservation

### 2.6 Fine Management

1. Go to **Fines** in the sidebar
2. View unpaid fines list with student details and amounts
3. When student pays in person → Click **💳 Settle**
4. Fine status changes to "Paid" with timestamp

**Process Overdue Fines (Batch):**
- Click **⚡ Process Overdue Fines** in the topbar
- Automatically creates fine records for all overdue active loans

### 2.7 Generating Reports

1. Go to **Reports** in the sidebar
2. Select report type: Borrowed / Overdue / Fines / Activity
3. Set date range (From / To) for filtered reports
4. Click **Generate**
5. Click **🖨 Print** to print the report

---

## Part 3: Admin Guide

### 3.1 Admin Dashboard

Shows:
- Total Users, Total Books, Active Loans, Students, Pending Fines, Overdue Books
- Recent Activity Log (last 10 entries)

### 3.2 User Management

**Create a User:**
1. Go to **User Management** → Click **+ Create User**
2. Fill in: Full Name, Username, Email, Password, Role
3. Click **✅ Create User**
4. New account appears in the user registry immediately

**Search/Filter Users:**
- Use search bar (name, username, email)
- Use role filter dropdown

**Delete a User:**
1. Find the user in the table
2. Click **🗑 Delete** → confirm dialog
3. The user is removed from the system

**Note:** You cannot delete your own admin account or other admin accounts.

### 3.3 Viewing Activity Logs

1. Go to **Activity Logs** in the sidebar
2. View all system events with: Timestamp, User, Role, Action, Description, IP
3. **Filter by date:** Enter From/To dates → click Filter
4. **Filter by action:** Enter keyword (e.g., LOGIN, BOOK_BORROWED)
5. Click **🖨 Print** to print the log

---

## Logging Out

Click **🚪 Logout** at the bottom of the sidebar from any page.

---

## Common Issues

| Problem | Solution |
|---------|---------|
| Cannot login | Check username/password; ask admin if account is deactivated |
| Book not available | Reserve it via the catalog |
| Fine on dashboard | Visit library to pay in person |
| "Unauthorized" redirect | You are trying to access a page outside your role |
