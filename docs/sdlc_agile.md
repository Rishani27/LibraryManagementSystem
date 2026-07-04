# ULMS — SDLC: Agile Methodology (5 Sprints)

## Methodology Overview

The ULMS project follows **Agile Scrum** with 5 two-week sprints. Each sprint produces a working, demonstrable increment. Daily standups, sprint reviews, and retrospectives are conducted at the end of each sprint.

---

## Sprint 0: Project Kick-off (Week 0)

**Duration:** 2 days  
**Goal:** Project setup and architecture design

### Activities
- Finalize SRS and scope
- Assign team roles (5 members × 1 FR each)
- Design database schema (ERD)
- Set up development environment (XAMPP)
- Create folder structure and core layer
- Establish Git repository and branching strategy

### Deliverables
- Approved SRS document
- Database schema.sql
- Core layer (Database.php, Session.php, Helper.php, config.php)
- Project folder structure

---

## Sprint 1: Foundation & Authentication (Week 1–2)

**Duration:** 2 weeks  
**Goal:** Working login system and user management

### Stories

| Story | Assignee | Points |
|-------|----------|--------|
| FR-01: Login page with role-based redirect | Member 1 | 5 |
| FR-02: Session guard (requireLogin per role) | Member 1 | 3 |
| FR-03: Admin dashboard with user stats | Member 1 | 5 |
| FR-03: Create librarian/student account | Member 1 | 8 |
| FR-03: Delete user account | Member 1 | 3 |
| FR-03: View user registry with search/filter | Member 1 | 5 |
| FR-12: Log login/logout events | Member 5 | 3 |

### Acceptance Criteria
- ✅ Admin can login and see dashboard
- ✅ Admin can create librarian and student accounts
- ✅ Admin can delete non-admin users
- ✅ Login/logout events appear in activity log
- ✅ Invalid credentials show error message
- ✅ RBAC prevents cross-role access

---

## Sprint 2: Book Catalog Management (Week 3–4)

**Duration:** 2 weeks  
**Goal:** Full book CRUD operational

### Stories

| Story | Assignee | Points |
|-------|----------|--------|
| FR-05: Librarian book catalog page | Member 2 | 5 |
| FR-05: Add book with validation | Member 2 | 8 |
| FR-05: Edit book details | Member 2 | 5 |
| FR-05: Delete book | Member 2 | 3 |
| FR-05: Search books (title/author/ISBN) | Member 2 | 5 |
| FR-05: Filter books by category | Member 2 | 3 |
| FR-12: Log book add/edit/delete | Member 5 | 3 |

### Acceptance Criteria
- ✅ Librarian can add books with all required fields
- ✅ Duplicate ISBN is rejected
- ✅ Search returns relevant results
- ✅ Category filter works correctly
- ✅ Deleted books are removed from catalog
- ✅ Book add/update/delete events logged

---

## Sprint 3: Borrowing & Return (Week 5–6)

**Duration:** 2 weeks  
**Goal:** Full circulation workflow

### Stories

| Story | Assignee | Points |
|-------|----------|--------|
| FR-06: Issue book to student | Member 3 | 13 |
| FR-06: Prevent issue when no copies available | Member 3 | 5 |
| FR-06: 14-day due date auto-calculation | Member 3 | 3 |
| FR-07: Return book processing | Member 3 | 8 |
| FR-07: Restore available copies on return | Member 3 | 3 |
| FR-09: Auto-create fine on overdue return | Member 4 | 8 |
| FR-12: Log borrow and return events | Member 5 | 3 |

### Acceptance Criteria
- ✅ Librarian can issue available books to students
- ✅ Available copies decrement on issue
- ✅ Due date = 14 days from issue date
- ✅ Return restores book availability
- ✅ Overdue return triggers fine (Rs. 10/day)
- ✅ Student cannot borrow same book twice
- ✅ Borrow/return events logged

---

## Sprint 4: Reservations & Fines (Week 7–8)

**Duration:** 2 weeks  
**Goal:** Reservation and fine workflow complete

### Stories

| Story | Assignee | Points |
|-------|----------|--------|
| FR-08: Student places reservation | Member 4 | 8 |
| FR-08: Prevent duplicate reservations | Member 4 | 3 |
| FR-08: Librarian fulfils reservation | Member 4 | 5 |
| FR-08: Librarian/Student cancels reservation | Member 4 | 3 |
| FR-09: Librarian views unpaid fines | Member 4 | 5 |
| FR-09: Settle fine (mark paid) | Member 4 | 5 |
| FR-09: Process overdue fines batch action | Member 4 | 8 |
| Student dashboard with overdue alerts | Member 3 | 5 |
| FR-12: Log reservation and fine events | Member 5 | 3 |

### Acceptance Criteria
- ✅ Student can only reserve unavailable books
- ✅ Duplicate pending reservations rejected
- ✅ Librarian can fulfil and cancel reservations
- ✅ Fine settlement marks fine as paid with timestamp
- ✅ Overdue alerts appear on student dashboard
- ✅ Fine/reservation events logged

---

## Sprint 5: Reports, Logs & Polish (Week 9–10)

**Duration:** 2 weeks  
**Goal:** Reports, admin logs, and production-ready polish

### Stories

| Story | Assignee | Points |
|-------|----------|--------|
| FR-11: Borrowed books report with date filter | Member 5 | 8 |
| FR-11: Overdue books report | Member 5 | 5 |
| FR-11: Fine report with date filter | Member 5 | 5 |
| FR-11: Activity log report with date filter | Member 5 | 5 |
| FR-11: Print support (CSS print media) | Member 5 | 3 |
| FR-12: Admin activity log viewer with filter | Member 5 | 5 |
| UI polish: consistent badges, animations | All | 5 |
| Cross-browser testing | All | 3 |
| Documentation: README, user manual, deploy | All | 8 |

### Acceptance Criteria
- ✅ All 4 report types display correctly with data
- ✅ Date range filtering works for all reports
- ✅ Print removes sidebar/buttons cleanly
- ✅ Admin can filter logs by date and action
- ✅ System works on Chrome, Firefox, Opera
- ✅ Full documentation package complete

---

## Sprint Velocity Summary

| Sprint | Story Points | Focus |
|--------|-------------|-------|
| Sprint 0 | 15 | Setup & Architecture |
| Sprint 1 | 32 | Auth & User Management |
| Sprint 2 | 32 | Book Catalog |
| Sprint 3 | 43 | Borrow & Return |
| Sprint 4 | 45 | Reservations & Fines |
| Sprint 5 | 47 | Reports & Polish |
| **Total** | **214** | |

---

## Definition of Done

A feature is considered "Done" when:
1. ✅ All layers implemented (Data → Business → Presentation)
2. ✅ Input validation in place
3. ✅ Activity logging integrated
4. ✅ Test cases written and passing
5. ✅ Code reviewed by at least one teammate
6. ✅ No SQL in presentation layer (architecture verified)
7. ✅ Works on Chrome, Firefox, Opera
