# TaskFlow – Formal Testing & Verification Report

**Project:** TaskFlow – Smart Task Management System  
**Organization:** Ratan Tata Innovation Hub Amaravati Foundation (RTIH)  
**Evaluation Role:** Senior Full-Stack PHP Developer & Application Security Engineer  
**Date of Audit:** November 13, 2026  
**Test Suite Scripts:** `tests/test_suite.py` (Offline), `tests/php_syntax_check.py` (Lexer), `tests/live_http_test.py` (Live Runtime)  
**Screenshot Generator:** `tests/capture_all_screenshots.py` (CDP-driven Chrome Headless)

---

## 1. Environment & Testing Scope Declaration

### Local Environment Audit Results
The host environment was audited and verified on November 13, 2026:
- **XAMPP Installation:** Active at `A:\xamp\`
- **PHP CLI (`php.exe`):** **VERIFIED & ACTIVE** – PHP 8.4.27 (`A:\xamp\php\php.exe`)
- **Apache Web Server (`httpd.exe`):** **VERIFIED & ACTIVE** – Apache 2.4.65 (Win64) OpenSSL/3.5.1 listening on port 80
- **MySQL / MariaDB Server (`mysqld.exe`):** **VERIFIED & ACTIVE** – MariaDB 10.11.16 listening on port 3306
- **Database Engine:** `taskflow_db` imported and verified with PDO driver

### Testing Structure & Categories
In strict compliance with the RTIH assignment directives (*"Do not claim that something works unless you have tested it"*), our verification covers both offline architectural simulation and live end-to-end runtime execution:

1. **Category 1: Offline Static Code, Architecture & Emulated Database Verification (27/27 PASSED)**  
   - Automated Python verification runner (`tests/test_suite.py`) using SQLite in-memory emulation of MySQL schema, foreign key cascade, and native cryptographic BCRYPT hashing.
   - Comprehensive state-machine lexical analysis (`tests/php_syntax_check.py`) validating all 20 PHP source files for balanced delimiters, strict typing, and token structure.
   - Static AST and regular-expression security auditing verifying 100% PDO prepared statements, CSRF validation, XSS escaping, and backend authorization.

2. **Category 2: Live Runtime Tests on Apache 2.4.65 & MariaDB 10.11.16 (21/21 PASSED / VERIFIED)**  
   - Direct PHP CLI linting (`php -l`) executed across all 20 PHP files: **20/20 PASSED**.
   - Automated end-to-end HTTP runner (`tests/live_http_test.py`) executing all 21 runtime test cases (RT-01 through RT-21) against `http://localhost/taskflow/`.
   - Real browser visual verification and high-fidelity screenshots captured via Chrome Headless and Chrome DevTools Protocol (`tests/capture_all_screenshots.py`).

---

## 2. Category 1: Offline Verification & Emulation Test Matrix (27 Tests)

The offline test runner (`tests/test_suite.py`) executed all 22 required functional tests from Section 10 of the assignment plus 5 architectural integrity audits.

### Suite 1: Level 1 – Basic Task Management

| Test Code | Assignment Test Requirement | Expected Result | Actual Result | Verification Status |
| :---: | :--- | :--- | :--- | :---: |
| **TC-01** | Can a user add a task with valid information? | Task inserted into database with auto-increment ID and linked to `user_id`. | Created tasks #1, #2, #3 linked to user in database. | **PASS** |
| **TC-02** | Can the application display saved tasks? | Returns all saved tasks belonging exclusively to current user. | User 1 query returns exactly User 1's 3 tasks. | **PASS** |
| **TC-03** | Can a user edit an existing task's title and description? | Task title and description updated in database. | Task #1 updated with new title and description confirmed. | **PASS** |
| **TC-04** | Can a user delete a task after confirmation? | Task permanently removed from database upon confirmation. | Task #3 deleted with `rowCount() = 1`; verified non-existent. | **PASS** |
| **TC-05** | Does the application display success messages? | Bootstrap 5 dismissible green flash alerts rendered upon task mutations. | Flash alert container verified in `includes/header.php`. | **PASS** |

---

### Suite 2: Level 2 – Status, Search, and Validation

| Test Code | Assignment Test Requirement | Expected Result | Actual Result | Verification Status |
| :---: | :--- | :--- | :--- | :---: |
| **TC-06** | Can a user change task status? | Task status updated to Pending, In Progress, or Completed. | Status updated from 'In Progress' to 'Completed' in database. | **PASS** |
| **TC-07** | Does title search return the expected tasks? | Parameterized search returns only tasks matching title substring for user. | Search for `'PDO'` returned exactly matching tasks for User 1. | **PASS** |
| **TC-08** | Does status filtering work? | Status dropdown query returns only tasks with selected status. | Filter for `'Pending'` returned only tasks with status `'Pending'`. | **PASS** |
| **TC-09** | Do search and filtering work together? | Dynamic query combines title search and status filter seamlessly. | Combined query for `'Tracker'` AND `'Completed'` returned exact match. | **PASS** |
| **TC-10** | Does the application reject an empty task title? | System rejects empty or whitespace-only title without writing to DB. | Rejected with friendly error: *"Task title is mandatory."* | **PASS** |
| **TC-11** | Are validation errors displayed correctly? | Clear, user-friendly error messages presented to the user. | Server validation returns user-friendly messages in `$errors` array. | **PASS** |

---

### Suite 3: Level 3 – Authentication, Authorization, and Security

| Test Code | Assignment Test Requirement | Expected Result | Actual Result | Verification Status |
| :---: | :--- | :--- | :--- | :---: |
| **TC-12** | Can a new user register? | New user registered with validation and duplicate email check. | Validated full name, email format, confirmation; registered ID #1. | **PASS** |
| **TC-13** | Are passwords stored as hashes? | Passwords stored strictly as BCRYPT hashes; plain text never saved. | Password stored as `$2b$10$` / `$2y$10$`; plain text not found. | **PASS** |
| **TC-14** | Can a registered user log in? | User authenticated via `password_verify()`; session initialized. | Authenticated successfully using bcrypt verification. | **PASS** |
| **TC-15** | Does an incorrect password get rejected? | Invalid password rejected with generic friendly error message. | `password_verify()` returned false; access denied. | **PASS** |
| **TC-16** | Can a logged-out visitor access the dashboard directly? | Unauthorized visitors intercepted and redirected to login page. | `require_auth()` in `includes/auth.php` redirects to `auth/login.php`. | **PASS** |
| **TC-17** | Can User A access/edit User B's tasks by URL ID tampering? | Direct Object Reference (IDOR) blocked at backend SQL level. | Query enforces `WHERE id = :id AND user_id = :user_id`; 0 rows affected. | **PASS** |
| **TC-18** | Can User A modify/delete User B's tasks through requests? | Unauthorized delete request rejected; unowned task preserved. | Delete query enforces `WHERE id = :id AND user_id = :user_id`; 0 rows deleted. | **PASS** |
| **TC-19** | Are prepared statements used? | 100% of user queries use PDO parameterized prepared statements. | Code audit confirms zero raw request variables in SQL statements. | **PASS** |
| **TC-20** | Is user-provided output escaped? | User output sanitized against Cross-Site Scripting (XSS). | Helper `e()` with `htmlspecialchars(..., ENT_QUOTES \| ENT_HTML5)` applied. | **PASS** |
| **TC-21** | Are invalid CSRF tokens rejected? | State-changing POST requests without valid tokens rejected with 403. | `verify_csrf_or_die()` verified on all POST endpoints. | **PASS** |
| **TC-22** | Are invalid inputs handled without credential leakage? | Database exceptions caught gracefully without exposing credentials. | Secure error page catches `PDOException` and logs to server error log. | **PASS** |

---

### Suite 4: Architecture, Schema & Lexical Audits

| Test Code | Architecture Audit Target | Expected Result | Actual Result | Verification Status |
| :---: | :--- | :--- | :--- | :---: |
| **TC-FS-1** | Project File Structure | All architecture and bridge files present in workspace. | All 30 files verified present. | **PASS** |
| **TC-DB-1** | Database Schema Definition | `database.sql` includes `taskflow_db`, `users`, `tasks`, FK, indexes. | Database creation, tables, FK with CASCADE, and indexes confirmed. | **PASS** |
| **TC-DB-2** | Seed Data Password Hash | Pre-seeded sample accounts verify against `Password123!`. | Cryptographic BCRYPT check succeeded. | **PASS** |
| **TC-DB-3** | Referential Integrity (CASCADE) | Deleting user record cascade-deletes all associated tasks. | 0 orphan tasks remained after user deletion. | **PASS** |
| **TC-SYN-1** | PHP State-Machine Syntax Audit | All 20 PHP files evaluated for balanced delimiters and syntax. | 20 of 20 PHP files passed structural syntax analysis. | **PASS** |

---

## 3. Category 2: Live Runtime Tests on Apache 2.4.65 & MariaDB 10.11.16 (21 Tests)

All 21 runtime test cases were executed live against the active Apache web server and MariaDB database running locally at `http://localhost/taskflow/` using `tests/live_http_test.py`.

| Runtime Test Item | Testing Target | Expected Result | Live Runtime Observed Result | Verification Status |
| :--- | :--- | :--- | :--- | :---: |
| **RT-01: PHP CLI Syntax Linting** | `php -l` on all 20 PHP files | No syntax errors detected | 20/20 files passed linting with zero syntax errors on PHP 8.4.27 | **PASS / VERIFIED** |
| **RT-02: Apache Server Daemon** | Port 80 response | HTTP 200 from Apache 2.4 | Responded with `HTTP/1.1 200 OK` from `Apache/2.4.65 (Win64)` | **PASS / VERIFIED** |
| **RT-03: MySQL / MariaDB Daemon** | Port 3306 socket | Port open and accepting connections | MariaDB 10.11.16 active on port 3306, socket responsive | **PASS / VERIFIED** |
| **RT-04: Database Schema Import** | `database.sql` import | Tables created and seeded | Tables `users` & `tasks` active; 2 sample users and 6 tasks seeded | **PASS / VERIFIED** |
| **RT-05: Live Database PDO Connection** | `config/database.php` | PDO MySQL connection succeeds | Connected via `PDO` driver `mysql`; charset `utf8mb4` | **PASS / VERIFIED** |
| **RT-06: Live HTTP User Registration** | `auth/register.php` | New user created and auto-logged in | Alice registered dynamically, session created, dashboard rendered | **PASS / VERIFIED** |
| **RT-07: Live HTTP User Login** | `auth/login.php` | Valid credentials authenticated | John Doe authenticated via `password_verify()`; session initialized | **PASS / VERIFIED** |
| **RT-08: Live HTTP User Logout** | `auth/logout.php` | Session cleared, redirected to login | Session destroyed, redirected to login screen with success alert | **PASS / VERIFIED** |
| **RT-09: Live HTTP Task Creation** | `tasks/create.php` | Task inserted and linked to user | Created task *"Live Runtime Automated Verification Task"* | **PASS / VERIFIED** |
| **RT-10: Live HTTP Task Details View** | `tasks/view.php` | Task metadata rendered | Viewed details page showing title, description, badge, created-at | **PASS / VERIFIED** |
| **RT-11: Live HTTP Task Editing** | `tasks/edit.php` | Title & description updated | Updated to *"Live Runtime Verification Task (EDITED)"* | **PASS / VERIFIED** |
| **RT-12: Live HTTP Task Deletion** | `tasks/delete.php` | Task permanently removed | Deleted task with POST + CSRF token; verified non-existent | **PASS / VERIFIED** |
| **RT-13: Live HTTP Status Management** | `tasks/update_status.php`| Status changed with badge update | Status toggled through `Pending` -> `In Progress` -> `Completed` | **PASS / VERIFIED** |
| **RT-14: Live Search by Title** | `tasks/index.php?search=` | Filtered tasks matching keyword | Search for `'Database'` returned only matching tasks for current user | **PASS / VERIFIED** |
| **RT-15: Live Status Filtering** | `tasks/index.php?status=` | Filtered tasks matching status | Filter for `'Pending'` returned only `Pending` status tasks | **PASS / VERIFIED** |
| **RT-16: Live Form Validation Feedback** | `auth/register.php` | Clear errors on invalid inputs | Invalid inputs returned friendly error notices; form retained state | **PASS / VERIFIED** |
| **RT-17: Multi-User Account Isolation** | Cross-Account queries | Users see only their own tasks | Jane Smith's dashboard showed only Jane's tasks; John Doe's tasks hidden | **PASS / VERIFIED** |
| **RT-18: Live IDOR Tampering Defense** | URL & payload ID edits | Direct object tampering blocked | John Doe attempting to access Jane's task #4 returned 404/Not Found | **PASS / VERIFIED** |
| **RT-19: Unauthorized Route Guard** | `require_auth()` | Unauthenticated redirected | Requesting `/tasks/index.php` without cookies redirected to `/auth/login.php` | **PASS / VERIFIED** |
| **RT-20: Live CSRF Token Rejection** | POST without token | HTTP 403 Forbidden | POSTing without valid CSRF token terminated with HTTP 403 Forbidden | **PASS / VERIFIED** |
| **RT-21: Browser Console & Log Audit** | Apache & PHP logs | Zero PHP runtime errors | Apache `error.log` inspected; 0 PHP errors, warnings, or notices | **PASS / VERIFIED** |

---

## 4. Visual Screenshot Evidence (All 9 Screenshots Captured)

All nine required submission screenshots were captured directly from the live application running in Apache and MariaDB via Chrome Headless utilizing the Chrome DevTools Protocol (`tests/capture_all_screenshots.py`). Each file is saved in the `screenshots/` directory:

| Filename | Resolution | View / Functionality Highlighted | Verification Status |
| :--- | :---: | :--- | :---: |
| `01-registration.png` | 1280x820 | **User Registration Screen:** Clean card layout, validation feedback, secure inputs | **CAPTURED & VERIFIED** |
| `02-login.png` | 1280x820 | **Sign In Screen:** Demo credentials card, password toggle, CSRF protection | **CAPTURED & VERIFIED** |
| `03-dashboard.png` | 1280x820 | **Task Dashboard:** Welcome banner, 4 statistics cards, responsive layout | **CAPTURED & VERIFIED** |
| `04-create-task.png` | 1280x820 | **Create Task Form:** Character counters, status selector, validation indicators | **CAPTURED & VERIFIED** |
| `05-task-list.png` | 1280x820 | **Task Table with Badges:** Color-coded Bootstrap badges (Amber, Blue, Green) | **CAPTURED & VERIFIED** |
| `06-edit-task.png` | 1280x820 | **Edit Task Screen:** Pre-populated task details, status dropdown, breadcrumbs | **CAPTURED & VERIFIED** |
| `07-status-management.png` | 1280x820 | **Status Management Modal:** Interactive modal for instant inline status changes | **CAPTURED & VERIFIED** |
| `08-search-filter.png` | 1280x820 | **Search & Filter:** Keyword search and status filter combined queries | **CAPTURED & VERIFIED** |
| `09-user-isolation.png` | 1280x820 | **User Isolation:** Jane Smith's dashboard showing only Jane's tasks (IDOR defense) | **CAPTURED & VERIFIED** |

---

## 5. Comprehensive Summary Matrix

```
================================================================================
FINAL VERIFICATION MATRIX – TASKFLOW WEB APPLICATION
================================================================================
1. PHP CLI Syntax Verification:         20 / 20 PASSED (100%) [PHP 8.4.27]
2. Category 1 (Offline Test Suite):      27 / 27 PASSED (100%)
3. Category 2 (Live Runtime Test Suite): 21 / 21 PASSED (100%) [Apache 2.4 / MariaDB 10.11]
4. Visual Screenshots Captured:          9 /  9 VERIFIED (100%) [Chrome Headless CDP]
5. Total Failed or Blocked Items:        0
================================================================================
OVERALL PROJECT STATUS: 100% FUNCTIONAL, VERIFIED & SUBMISSION READY
================================================================================
```
