# ULMS — Troubleshooting Guide (50+ Issues)

---

## 🔴 Installation Issues

### 1. Cannot import schema.sql in phpMyAdmin
**Cause:** File size limit or timeout  
**Fix:** MySQL config → `upload_max_filesize = 64M`, `max_execution_time = 300`

### 2. "Access denied for user 'root'@'localhost'"
**Cause:** MySQL password mismatch  
**Fix:** Update `DB_PASS` in `app/config/config.php`

### 3. "Unknown database 'ulms_db'"
**Cause:** Database not created before import  
**Fix:** Create `ulms_db` in phpMyAdmin first, then import

### 4. setup.php shows blank page
**Cause:** PHP errors hidden  
**Fix:** Set `DEBUG_MODE = true` in config.php temporarily

### 5. "Class 'PDO' not found"
**Cause:** PDO extension not loaded  
**Fix:** Enable `php_pdo_mysql.dll` in php.ini, restart Apache

### 6. 500 Internal Server Error on first load
**Cause:** File permissions or .htaccess issue  
**Fix:** Set folder permissions to 755; check Apache mod_rewrite enabled

### 7. Apache says "403 Forbidden" on public/
**Cause:** Apache directory permissions  
**Fix:** Add `Require all granted` to Apache VirtualHost config

### 8. "XAMPP page" shows instead of login
**Cause:** Not pointing to correct directory  
**Fix:** URL should be `http://localhost/library-system/public/login.php`

---

## 🔴 Login / Authentication Issues

### 9. Correct credentials not working
**Cause:** Admin password hash not generated  
**Fix:** Run `setup.php` again to reset the admin password

### 10. "Session has expired" after short time
**Cause:** SESSION_LIFETIME set too low  
**Fix:** Increase `SESSION_LIFETIME` constant in config.php

### 11. Redirect loop on login
**Cause:** Session not starting properly  
**Fix:** Ensure `session_name(SESSION_NAME)` is called before `session_start()`; check `SESSION_NAME` constant

### 12. "Unauthorized" after login
**Cause:** Role mismatch — user has wrong role in database  
**Fix:** Check user's role in `users` table via phpMyAdmin

### 13. Cannot logout — keeps session alive
**Cause:** Browser caching old session  
**Fix:** Clear browser cookies and local storage, then retry

### 14. Student sees librarian pages
**Cause:** `requireLogin()` not called on presentation page  
**Fix:** Verify every presentation page has `Session::requireLogin('correct_role')` at top

---

## 🔴 Book Catalog Issues

### 15. Book not appearing after add
**Cause:** Form POST failed silently  
**Fix:** Enable DEBUG_MODE to see errors; check flash message in session

### 16. "ISBN already exists" when adding new book
**Cause:** ISBN UNIQUE constraint in database  
**Fix:** Check if another book has same ISBN; leave ISBN blank if unknown

### 17. Book count not updating after delete
**Cause:** Browser cache  
**Fix:** Hard refresh (Ctrl+Shift+R); or check JS confirm dialog not blocking form

### 18. Edit modal not populating fields
**Cause:** JavaScript `fillModal()` function called with wrong data  
**Fix:** Check for special characters in title (apostrophes break `addslashes`); use proper escaping

### 19. Search returns no results for existing book
**Cause:** LIKE query not matching due to trailing spaces  
**Fix:** Ensure `trim()` is applied to search input

### 20. Category dropdown missing new categories
**Cause:** `getAllCategories()` uses DISTINCT — new categories appear only after books added  
**Fix:** Normal behavior; add a book with the new category first

---

## 🔴 Borrowing / Return Issues

### 21. "No available copies" but book count shows > 0
**Cause:** `available_copies` vs `total_copies` mismatch in database  
**Fix:** Run: `UPDATE books SET available_copies = total_copies - (SELECT COUNT(*) FROM loans WHERE book_id = books.id AND status='active');`

### 22. Loan created but copies not decremented
**Cause:** Transaction rollback due to error  
**Fix:** Enable DEBUG_MODE; check MySQL foreign key constraints

### 23. Return processing fails
**Cause:** Loan ID mismatch or status already 'returned'  
**Fix:** Reload the return page to get fresh loan list

### 24. Fine not created on overdue return
**Cause:** `calculateOverdueDays()` returning 0 due to timezone issue  
**Fix:** Ensure `date_default_timezone_set()` is called in config.php

### 25. Due date showing wrong date
**Cause:** Server timezone mismatch  
**Fix:** Set `date_default_timezone_set('Asia/Colombo')` (or your local timezone) in config.php

### 26. Overdue row not highlighted in red
**Cause:** `data-overdue` attribute not set  
**Fix:** Check `isOverdue()` method in Loan model; ensure CSS `tr.overdue-row` class applied via JS

---

## 🔴 Reservation Issues

### 27. Student can reserve available book
**Cause:** `isAvailable()` check not working  
**Fix:** Verify `available_copies > 0` logic in `ReservationService::reserve()`

### 28. Reservation not appearing for librarian
**Cause:** Filter set to "Pending Only" but status is not pending  
**Fix:** Switch filter to "All" to see all reservations

### 29. Cannot cancel reservation as student
**Cause:** POST action `cancel` hitting wrong page  
**Fix:** Verify form `action` attribute points to `my_reservations.php`

### 30. Fulfilled reservation not converting to loan automatically
**Cause:** Fulfilling a reservation only marks it done — librarian must issue separately  
**Fix:** This is by design — librarian fulfils reservation, then uses Issue Books to create the loan

---

## 🔴 Fine Issues

### 31. Fine amount is 0 even though book is overdue
**Cause:** `calculateOverdueDays()` returning 0 due to incorrect date comparison  
**Fix:** Ensure date format is `Y-m-d` consistently in loans table

### 32. Settle fine button missing
**Cause:** Fine status already 'paid' or wrong filter  
**Fix:** Check filter; if fine is unpaid and button missing, check HTML render in `fines.php`

### 33. "Process Overdue Fines" creates duplicate fines
**Cause:** `findByLoan()` check failing  
**Fix:** Verify `FineRepository::findByLoan()` JOIN is correct; check for null return

### 34. Fine total shows wrong amount
**Cause:** Float precision issue  
**Fix:** Use `number_format($amount, 2)` consistently in Helper::formatCurrency()

---

## 🔴 Reports Issues

### 35. Report shows no data for date range
**Cause:** Date range doesn't match database dates  
**Fix:** Check `issue_date` column uses DATE format (`Y-m-d`); adjust date picker

### 36. Print cuts off table columns
**Cause:** CSS print media not applied  
**Fix:** Ensure print CSS in `style.css` is loaded; try landscape mode in print dialog

### 37. Activity report showing system entries
**Cause:** System logs (like setup.php seed) appear in logs  
**Fix:** Filter by specific role or action keyword to exclude

### 38. Reports load slowly with large datasets
**Cause:** No LIMIT on report queries with 1000+ records  
**Fix:** Use date range filter to limit results; indexes on `created_at` columns are present

---

## 🔴 UI / Browser Issues

### 39. Sidebar not appearing
**Cause:** CSS file not loading  
**Fix:** Check `ROOT_URL` constant in the page; inspect browser console for 404 on CSS

### 40. Modal not opening on click
**Cause:** JavaScript file not loaded  
**Fix:** Check footer.php is included; verify script.js path in footer

### 41. Flash message not appearing
**Cause:** Session flash not set or page not doing `Session::getFlash()`  
**Fix:** Verify presentation page calls `$flash = Session::getFlash()` before HTML output

### 42. Delete confirmation not showing
**Cause:** Browser blocking JavaScript `confirm()` dialogs  
**Fix:** Allow popups for localhost in browser settings

### 43. Overdue rows not highlighted in red
**Cause:** `data-overdue="1"` attribute not set  
**Fix:** Check borrow.php/return.php templates for `<?= $isOverdue ? 'data-overdue="1"' : '' ?>`

### 44. Page shows PHP source code instead of rendered HTML
**Cause:** PHP not configured in Apache  
**Fix:** Enable PHP module: `sudo a2enmod php8.1`; check .php file association in httpd.conf

### 45. CSRF vulnerability
**Cause:** No CSRF tokens implemented  
**Fix:** Add hidden CSRF token field to all POST forms; validate on server

### 46. "headers already sent" PHP error
**Cause:** Output before `header()` call  
**Fix:** Ensure no whitespace/BOM before `<?php` in any included file; check all includes

### 47. Session data lost between requests
**Cause:** Cookie path mismatch  
**Fix:** Set `session_set_cookie_params(['path' => '/'])` before `session_start()`

### 48. Database connection pooling issues under load
**Cause:** PDO singleton re-created  
**Fix:** `Database::getInstance()` is singleton by design — ensure it's only instantiated once per request

### 49. Long book titles break table layout
**Cause:** No max-width or overflow on table cells  
**Fix:** Add `max-width: 200px; overflow: hidden; text-overflow: ellipsis;` to `td` CSS

### 50. Mobile sidebar overlaps content
**Cause:** Responsive CSS not triggering correctly  
**Fix:** Check viewport meta tag is present in header.php; sidebar CSS uses `transform: translateX(-260px)` at ≤768px

---

## 🟢 Quick Recovery Commands (MySQL)

```sql
-- Check available copies consistency
SELECT b.id, b.title, b.available_copies,
       b.total_copies - COUNT(l.id) AS calculated_available
FROM books b
LEFT JOIN loans l ON l.book_id = b.id AND l.status = 'active'
GROUP BY b.id;

-- Reset available copies
UPDATE books b
SET available_copies = b.total_copies - (
    SELECT COUNT(*) FROM loans l
    WHERE l.book_id = b.id AND l.status = 'active'
);

-- Clear all logs (testing only!)
DELETE FROM activity_logs;

-- Reset all fines to unpaid (testing only!)
UPDATE fines SET status = 'unpaid', settled_at = NULL, settled_by = NULL;
```
