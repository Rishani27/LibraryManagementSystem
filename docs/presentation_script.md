# ULMS — Project Presentation Script (10–15 Minutes)

## Viva / Presentation Guide for Final Year Submission

---

## 🎤 INTRODUCTION (1–2 minutes)

> *"Good [morning/afternoon], distinguished panel. My name is [Name], and I am representing our five-member team for the University Library Management System — or ULMS.*
>
> *Our system is a web-based application built using PHP, MySQL, HTML, and CSS. It automates all core operations of a university library: book cataloging, borrowing, returns, reservations, fines, reporting, and activity tracking.*
>
> *Most importantly — and I want to emphasize this — our system strictly follows Layered Architecture with four completely separated layers: Presentation, Business, Data, and Core. No SQL exists in our UI pages. No HTML exists in our service classes. The architecture is strictly enforced across every single file in the project."*

---

## 🏗️ ARCHITECTURE EXPLANATION (2–3 minutes)

> *"Let me walk you through our architecture. We have four layers:*
>
> *First — the **Core Layer**. This is the foundation. It contains our PDO database singleton, our session manager with role-based access guards, and our helper utilities. Nothing in this layer knows about HTTP requests or library business rules.*
>
> *Second — the **Data Layer**. This contains six repositories — one per entity — that handle only database queries using PDO prepared statements. We also have six model classes that are plain PHP data transfer objects. No business logic here.*
>
> *Third — the **Business Layer**. Nine service classes implement all business rules. For example, BorrowService enforces the 14-day loan period, checks book availability, and wraps the loan creation and copy decrement in a database transaction. Nothing in this layer outputs HTML.*
>
> *Fourth — the **Presentation Layer**. Our PHP view files only handle receiving POST input, calling service methods, and rendering HTML. They contain zero SQL and zero business rules.*
>
> *This means if we want to change the fine rate from Rs. 10 to Rs. 15, we change one constant in config.php. If we want to change the UI completely, we don't touch any business logic. The architecture makes the system maintainable and scalable."*

---

## 👥 TEAM DISTRIBUTION (1 minute)

> *"Our five members each owned a complete vertical slice of the system:*
>
> - *Member 1 built FR-03: the entire User Management module — creating, deleting, and searching user accounts*
> - *Member 2 built FR-05: the Book Catalog — add, edit, delete, search, and filter books*
> - *Member 3 built FR-06 and FR-07: the Borrowing and Return workflow with automatic fine calculation*
> - *Member 4 built FR-08 and FR-09: Reservations and Fine Management*
> - *Member 5 built FR-11 and FR-12: the Reporting system and Activity Logging*
>
> *Each member implemented all three layers for their feature — Data, Business, and Presentation — ensuring equal workload distribution."*

---

## 💻 LIVE DEMONSTRATION (4–5 minutes)

### Admin Demonstration
> *"Let me demonstrate the system live. I'll start as the administrator.*
>
> *I login with admin credentials — notice the role-based redirect immediately takes me to the Admin Dashboard. I can see: total users, total books, active loans, pending fines, and recent security logs.*
>
> *Now I'll create a new student account — [create user]. The system validates input, hashes the password with bcrypt, and immediately logs the 'USER_CREATED' event in the activity log.*
>
> *Let me show you the Activity Logs page — every action in the system is recorded with the user, role, timestamp, and IP address. This supports our security requirement."*

### Librarian Demonstration
> *"Now I'll login as the librarian.*
>
> *From the Librarian Dashboard, I can see active loans, overdue warnings, pending reservations, and settled fines.*
>
> *Let me add a book — [add book]. Notice the validator rejects empty titles or invalid ISBNs immediately.*
>
> *Now I'll issue this book to a student — [issue book]. The system sets the due date automatically to 14 days, decrements available copies, and logs the borrow event.*
>
> *Let me simulate a return — [return book]. Since it's overdue by 3 days, the system automatically calculates Rs. 30 fine and creates the fine record.*
>
> *Now I'll generate a report — [show overdue report]. Date range filtering works, and the Print button removes the sidebar for a clean printout."*

### Student Demonstration
> *"Finally, as a student, I see my dashboard with my active loans and an overdue alert banner.*
>
> *In the catalog, I can browse and search books. If a book has zero copies, I can reserve it with one click — [reserve book]. The reservation appears immediately in My Reservations."*

---

## 📊 DATABASE DESIGN (1 minute)

> *"Our database has six tables: users, books, loans, reservations, fines, and activity_logs.*
>
> *All foreign key relationships use InnoDB with referential integrity constraints. We use PDO prepared statements throughout — there is no possibility of SQL injection in this system.*
>
> *We also added performance indexes on frequently queried columns: loan status, student ID, fine status, and log timestamps."*

---

## 🔐 SECURITY FEATURES (30 seconds)

> *"On security: we use bcrypt with cost 12 for password hashing — an industry standard. Sessions are protected with HTTP-only cookies, SameSite=Lax, and session ID regeneration on login to prevent session fixation attacks. Every user input is sanitized with htmlspecialchars before output to prevent XSS. And all 12 types of activity events are logged for audit purposes."*

---

## 📋 SDLC APPROACH (30 seconds)

> *"We followed Agile Scrum with 5 two-week sprints. Sprint 1 covered authentication and user management. Sprint 2: book catalog. Sprint 3: borrow and return. Sprint 4: reservations and fines. Sprint 5: reports, logging, and polish.*
>
> *We used a Definition of Done that required each feature to have all three layers implemented, validated, and logged before it was marked complete."*

---

## ✅ CONCLUSION (30 seconds)

> *"In summary, ULMS is a production-ready university library system that:*
>
> - *Strictly enforces Layered Architecture — no layer violations exist*
> - *Fully implements all 12 functional requirements from the SRS*
> - *Supports all three roles with appropriate access control*
> - *Uses secure PHP practices including bcrypt, PDO, and session management*
> - *Includes comprehensive documentation: UML diagrams, Agile sprints, 54 test cases, user manuals, deployment guides, and 50+ troubleshooting solutions*
>
> *Thank you. We are ready for questions."*

---

## ❓ EXPECTED VIVA QUESTIONS & ANSWERS

| Question | Suggested Answer |
|----------|-----------------|
| Why Layered Architecture? | Separation of concerns — easier to maintain, test, and extend each layer independently |
| How is SQL injection prevented? | PDO prepared statements with parameterized queries throughout the Data Layer |
| How are passwords stored? | bcrypt with cost factor 12 — never plaintext |
| What happens if book is overdue? | ReturnService calculates days overdue, FineService creates fine at Rs. 10/day |
| Can a student see another student's data? | No — all queries filter by `Session::getUserId()` |
| How is the architecture verified? | All SQL only in repository files; all business rules only in service files; verified manually in code review |
| What if two librarians issue the same last copy simultaneously? | Database transaction with `available_copies > 0` check in UPDATE; only one succeeds |
| How would you add email notifications? | Add `NotificationService` in the Business Layer that calls a mailer — no other layers change |
| What is the fine calculation formula? | `overdue_days × Rs. 10` calculated by `Helper::calculateFine()` using `Helper::calculateOverdueDays()` |
| How does RBAC work? | `Session::requireLogin('role')` called at the top of every page; redirects if role doesn't match |
