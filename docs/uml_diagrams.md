# ULMS — UML Diagrams (Descriptions)

## 1. Use Case Diagram

### Actors
- **Admin** — System manager
- **Librarian** — Operations manager
- **Student** — Library user

### Use Cases

**Admin:**
- Login / Logout
- Create User Account (Librarian / Student)
- Delete User Account
- View User Registry
- View Activity Logs
- View Admin Dashboard

**Librarian:**
- Login / Logout
- Add / Edit / Delete Book
- Search / Filter Books
- Issue Book to Student
- Process Book Return
- Manage Reservations (Fulfil / Cancel)
- Settle Student Fine
- Process Overdue Fines
- Generate Reports (Borrowed / Overdue / Fine / Activity)
- View Librarian Dashboard

**Student:**
- Login / Logout
- Browse Book Catalog
- Search Books
- Place Reservation
- Cancel Reservation
- View My Loans
- View My Fines
- View Student Dashboard

---

## 2. Class Diagram

### Core Layer
```
Database           Session              Helper
─────────          ───────              ──────
-instance          -started             +e(v):string
+getInstance()     +start()             +redirect(url)
+getConnection()   +set(k,v)            +formatDate(d)
+prepare(sql)      +get(k)              +calculateOverdueDays(due)
+lastInsertId()    +login(user)         +calculateFine(days)
+beginTransaction()  +isLoggedIn()      +formatCurrency(amt)
+commit()          +requireLogin()      +getIp()
+rollBack()        +flash(type,msg)     +loanStatusBadge(s)
                   +destroy()
```

### Data Layer Models (DTOs)
```
User               Book                  Loan
────               ────                  ────
+id                +id                   +id
+username          +title                +book_id
+password          +author               +student_id
+full_name         +isbn                 +issued_by
+email             +category             +issue_date
+role              +total_copies         +due_date
+is_active         +available_copies     +return_date
                   +isAvailable():bool   +status
                                         +isOverdue():bool
```

```
Reservation        Fine                  ActivityLog
───────────        ────                  ───────────
+id                +id                   +id
+book_id           +loan_id              +user_id
+student_id        +student_id           +username
+reserved_at       +amount               +role
+status            +overdue_days         +action
+fulfilled_at      +status               +description
                   +settled_at           +ip_address
                   +settled_by           +created_at
```

### Repository Layer
```
UserRepository     BookRepository        LoanRepository
──────────────     ──────────────        ──────────────
-db:PDO            -db:PDO               -db:PDO
+findById()        +findById()           +findById()
+findByUsername()  +findAll()            +findAll()
+findAll()         +search()             +findByStudent()
+count()           +filterByCategory()   +findActive()
+create()          +getAllCategories()    +findOverdue()
+update()          +create()             +countActive()
+delete()          +update()             +create()
+search()          +decrementAvailable() +markReturned()
                   +incrementAvailable() +findByDateRange()
                   +delete()
```

### Service Layer
```
AuthService        UserService           BookService
───────────        ───────────           ───────────
-userRepo          -repo                 -repo
-logService        -validator            -validator
+login()           -logService           -logService
+logout()          +createUser()         +addBook()
+getDashboardUrl() +deleteUser()         +updateBook()
                   +getAllUsers()         +deleteBook()
                   +searchUsers()        +searchBooks()
                   +getUserStats()       +getBookStats()
```

```
BorrowService      ReturnService         ReservationService
─────────────      ─────────────         ──────────────────
-loanRepo          -loanRepo             -repo
-bookRepo          -bookRepo             -bookRepo
-userRepo          -fineRepo             -logService
-validator         -logService           +reserve()
-logService        +returnBook()         +fulfil()
+issueBook()       +getActiveLoans()     +cancel()
+getActiveLoans()  +getLoanById()        +getAllReservations()
+getOverdueLoans()                       +getPendingReservations()
```

```
FineService        ReportService         LogService
───────────        ─────────────         ──────────
-repo              -loanRepo             -repo
-loanRepo          -fineRepo             +log(action,desc)
-logService        -resRepo              +getAll()
+settleFine()      -logRepo              +getByUser()
+getUnpaidFines()  +getBorrowedReport()  +getByAction()
+processOverdue()  +getOverdueReport()   +getByDateRange()
+getDashboard()    +getFineReport()
                   +getActivityReport()
                   +getSummaryStats()
```

---

## 3. Sequence Diagram — Book Borrowing

```
Librarian    borrow.php    BorrowService    BookRepository    LoanRepository    Database
    │              │              │                │                 │               │
    │──POST──────→ │              │                │                 │               │
    │              │──issueBook()→│                │                 │               │
    │              │              │──findById(id)──→                 │               │
    │              │              │←── Book obj ───│                 │               │
    │              │              │──isAvailable()──(check copies)   │               │
    │              │              │──beginTransaction()──────────────────────────────→
    │              │              │──create(loanData)──────────────→ │               │
    │              │              │                                   │──INSERT──────→
    │              │              │──decrementAvailable(bookId)──────→│              │
    │              │              │                                   │──UPDATE──────→
    │              │              │──commit()────────────────────────────────────────→
    │              │              │──log('BOOK_BORROWED')             │               │
    │              │←── result ───│                │                 │               │
    │←─ flash+redirect            │                │                 │               │
```

---

## 4. Sequence Diagram — Student Login

```
Student    login.php    AuthService    UserRepository    Session    LogService
   │           │              │               │              │           │
   │──POST────→│              │               │              │           │
   │           │──login(u,p)──→              │              │           │
   │           │              │──findByUsername()───────────→           │
   │           │              │←── User obj ──│              │           │
   │           │              │──password_verify()            │           │
   │           │              │──Session::login(user)────────→           │
   │           │              │──log('LOGIN')─────────────────────────→  │
   │           │←── result ───│               │              │           │
   │←─ redirect to dashboard  │               │              │           │
```

---

## 5. Activity Diagram — Fine Processing

```
Start
  ↓
Book Returned? → No → Continue monitoring loans
  ↓ Yes
Calculate Overdue Days (return_date - due_date)
  ↓
Overdue Days > 0?
  ├─ No → Return recorded, no fine
  └─ Yes → Fine = Days × Rs.10
            ↓
          Insert fine record (status = unpaid)
            ↓
          Notify on dashboard
            ↓
          Librarian sees unpaid fines list
            ↓
          Student pays → Librarian clicks "Settle"
            ↓
          Fine status = paid, settled_at = NOW()
            ↓
          Log 'FINE_SETTLED'
            ↓
          End
```

---

## 6. ERD (Entity-Relationship Description)

```
users (id PK, username UNIQUE, password, full_name, email UNIQUE, role, is_active, timestamps)

books (id PK, title, author, isbn UNIQUE NULL, category, total_copies, available_copies, timestamps)

loans (id PK,
       book_id FK→books.id,
       student_id FK→users.id,
       issued_by FK→users.id,
       issue_date, due_date, return_date NULL,
       status ENUM(active,returned,overdue),
       created_at)

reservations (id PK,
              book_id FK→books.id,
              student_id FK→users.id,
              reserved_at, status ENUM(pending,fulfilled,cancelled), fulfilled_at NULL)

fines (id PK,
       loan_id FK→loans.id,
       student_id FK→users.id,
       amount, overdue_days,
       status ENUM(unpaid,paid),
       settled_at NULL, settled_by FK→users.id NULL,
       created_at)

activity_logs (id PK,
               user_id FK→users.id NULL,
               username, role, action, description, ip_address, created_at)
```

### Relationships
- One `user` → many `loans` (as student)
- One `user` → many `loans` (as issuing librarian)
- One `book` → many `loans`
- One `loan` → one `fine` (at most)
- One `user` → many `reservations` (as student)
- One `book` → many `reservations`
- One `user` → many `activity_logs`
