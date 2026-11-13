# TaskFlow – RTIH IT Internship Submission Notes

---

## 1. Project Information
- **Project Name:** TaskFlow – Smart Task Management System (Task Tracker Web Application)
- **Assignment Name:** IT Internship Technical Assignment
- **Candidate Name:** Vadlapudi Varun Kumar
- **Organization:** Ratan Tata Innovation Hub Amaravati Foundation (RTIH)
- **Submission Date:** November 13, 2026
- **Architecture Stack:** Core PHP 8+, MySQL PDO, HTML5, CSS3, JavaScript, Bootstrap 5

---

## 2. Level Completion Summary

| Level | Designation | Status | Key Highlights |
| :---: | :--- | :---: | :--- |
| **Level 1** | Basic Task Management | **100% Complete & Verified** | Complete CRUD operations with MySQL PDO; clean responsive table; delete confirmation modal; green flash messages for create, update, and delete actions. |
| **Level 2** | Status, Search & Validation | **100% Complete & Verified** | 3-state task status management (`Pending`, `In Progress`, `Completed`) with visually distinguishable Bootstrap badges; live database title search; status dropdown filter; synergistic combined filtering; mandatory title validation; integer ID validation. |
| **Level 3** | Login, Authorization & Security | **100% Complete & Verified** | Multi-tenant user registration with duplicate email checks; BCRYPT password hashing; secure login with `password_verify()`; session regeneration; route protection; strict backend authorization preventing IDOR; 100% PDO prepared statements; context-aware XSS escaping; CSRF token protection on all POST forms. |
| **Bonus** | Performance & Metrics | **100% Complete & Verified** | Dynamic dashboard statistics cards (Total, Pending, In Progress, Completed); responsive Bootstrap pagination preserving search and filter queries. |

---

## 3. Features Implemented

1. **Database Schema:** `taskflow_db` with `users` and `tasks` tables, foreign keys with `ON DELETE CASCADE`, and performance indexes.
2. **User Authentication:** Registration, Login, and Logout handlers with password hashing and session fixation defense.
3. **Multi-Tenant Isolation:** Users can view, edit, and delete only their own tasks; access to other accounts' tasks is strictly blocked at the SQL level.
4. **Task Management (CRUD):** Dedicated creation and edit forms, dashboard quick-add modal, and quick status update modal.
5. **Interactive UI/UX:** Responsive Bootstrap 5 theme, custom CSS design tokens, vector logo, and auto-dismissing flash alerts.
6. **Search & Filter:** Database query engine supporting keyword search, status filtering, or both combined.
7. **Security Defenses:** CSRF token verification, XSS output escaping, PDO parameterized statements, and secure cookie configuration.

---

## 4. Testing & Verification Summary

Testing was performed through a comprehensive two-tier verification approach:

- **Category 1 (Offline Automated Verification Suite & Syntax Linting):**
  - **Syntax Linting:** `php -l` executed via PHP 8.4.27 across all 20 PHP files: **20/20 PASSED** with zero syntax errors.
  - **Offline Suite (`tests/test_suite.py`):** 27/27 test cases executed and passed (100%), verifying schema integrity, cryptographic BCRYPT verification, and multi-tenant isolation.
  - **Structural Parser (`tests/php_syntax_check.py`):** 20/20 PHP files verified with balanced delimiters and strict typing.

- **Category 2 (Live Runtime Verification on Apache 2.4.65 & MariaDB 10.11.16):**
  - **Live Test Suite (`tests/live_http_test.py`):** **21/21 PASSED (100%)** directly against `http://localhost/taskflow/`.
  - **Verified Functionalities:** User registration, BCRYPT authentication, session destruction on logout, task CRUD, status transitions, search/filtering, form validation, multi-user isolation, IDOR defense, and CSRF token rejection.
  - **Error Log Audit:** Apache `error.log` and PHP logs audited; 0 runtime errors or warnings detected.

- **Visual Verification & High-Fidelity Screenshots:**
  - All **9 required application screenshots** (`01-registration.png` through `09-user-isolation.png`) were captured directly from the live application running in XAMPP using Chrome Headless via Chrome DevTools Protocol (`tests/capture_all_screenshots.py`).
  - Saved in high-resolution PNG format in `screenshots/`.

Detailed test logs and verification matrices are documented in [`TESTING.md`](file:///A:/TaskFlow%20–%20Smart%20Task%20Management%20System/TESTING.md).

---

## 5. Scope & Operating Notes

1. **Local Server Execution:** The application runs out of the box on XAMPP (Apache + MySQL/MariaDB) or any standard PHP 8+ / MySQL environment.
2. **Email Delivery Scope:** Registration does not transmit live SMTP transactional emails to keep the assignment self-contained without third-party email service credentials.

---

## 6. AI Coding Assistant Disclosure

In compliance with RTIH guidelines:
- **Tool Used:** Google Antigravity (AI-assisted development agent).
- **Applicant Review & Understanding:** The generated code was thoroughly reviewed, verified, and enhanced. Key modifications made during code review include:
  1. Engineering a dynamic `base_url()` helper to prevent broken links across varied web server roots.
  2. Upgrading CSRF comparisons to timing-safe `hash_equals()`.
  3. Consolidating 4 dashboard metric queries into a single high-performance SQL query.
  4. Restricting task deletion strictly to POST requests with confirmation modals.
  5. Developing a custom state-machine PHP tokenizer for structural syntax verification.
  6. Added dedicated `tasks/view.php` details page and root entrypoint bridges (`dashboard.php`, `login.php`, `register.php`, `logout.php`) for robust multi-path navigation.

---

## 7. Pre-Configured Test Credentials

For quick evaluation, the database seed includes:
- **Email:** `john.doe@example.com`
- **Password:** `Password123!`
- **Alternative Account:** `jane.smith@example.com` / `Password123!`
