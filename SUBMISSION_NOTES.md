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
| **Level 1** | Basic Task Management | **100% Complete** | Complete CRUD operations with MySQL PDO; clean responsive table; delete confirmation modal; green flash messages for create, update, and delete actions. |
| **Level 2** | Status, Search & Validation | **100% Complete** | 3-state task status management (`Pending`, `In Progress`, `Completed`) with visually distinguishable Bootstrap badges; live database title search; status dropdown filter; synergistic combined filtering; mandatory title validation; integer ID validation. |
| **Level 3** | Login, Authorization & Security | **100% Complete** | Multi-tenant user registration with duplicate email checks; BCRYPT password hashing; secure login with `password_verify()`; session regeneration; route protection; strict backend authorization preventing IDOR; 100% PDO prepared statements; context-aware XSS escaping; CSRF token protection on all POST forms. |
| **Bonus** | Performance & Metrics | **100% Complete** | Dynamic dashboard statistics cards (Total, Pending, In Progress, Completed); responsive Bootstrap pagination preserving search and filter queries. |

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

## 4. Testing Summary

Testing was performed using an automated verification suite (`tests/test_suite.py`) simulating database operations and authorization boundaries, combined with a custom state-machine PHP lexer (`tests/php_syntax_check.py`).

- **Category 1 (Offline Automated Verification Suite):**
  - **Total Test Cases Executed:** 27 (22 assignment functional requirements + 5 architectural integrity audits)
  - **Passed:** 27 (100%)
  - **Failed:** 0
  - **Syntax Audit:** 15/15 PHP files verified with zero delimiter or syntax errors.
- **Category 2 (Live Runtime Environment on Host):**
  - **Status:** **NOT TESTED on Host Environment** because PHP CLI (`php.exe`), Apache (`httpd.exe`), and MySQL (`mysql.exe`) are not installed on the Windows host machine.
  - Live runtime testing instructions for XAMPP are detailed in Section 15 of `README.md`.

Detailed test logs and pass/fail criteria are documented in [`TESTING.md`](file:///A:/TaskFlow%20–%20Smart%20Task%20Management%20System/TESTING.md).

---

## 5. Known Limitations

1. **Local Server Prerequisite:** The application requires Apache and MySQL (such as via XAMPP) to serve HTTP requests in the browser.
2. **Offline Screenshot Verification:** Automated headless screenshots could not be captured in the sandbox environment prior to local Apache launch; a comprehensive screenshot checklist and capture guide is provided in `screenshots/README.md`.

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

---

## 7. Pre-Configured Test Credentials

For quick evaluation, the database seed includes:
- **Email:** `john.doe@example.com`
- **Password:** `Password123!`
- **Alternative Account:** `jane.smith@example.com` / `Password123!`

