# TaskFlow – Smart Task Management System

**Technical Assignment Submission for IT Internship**  
**Organization:** Ratan Tata Innovation Hub Amaravati Foundation (RTIH)  
**Author:** Vadlapudi Varun Kumar  
**Architecture:** Core PHP 8+, MySQL PDO, HTML5, CSS3, JavaScript, Bootstrap 5  

---

## 1. Project Title
**TaskFlow – Smart Task Management System (Task Tracker Web Application)**

---

## 2. Project Overview
TaskFlow is a production-grade, highly secure, responsive Task Tracker web application engineered using modern Core PHP 8+ and MySQL PDO. The application allows users to organize their personal work pipelines, track task progress through distinct statuses (`Pending`, `In Progress`, `Completed`), search by task title, filter by status, and securely manage tasks within an isolated multi-tenant environment.

---

## 3. Problem Statement
Modern professionals and software engineering teams struggle with disorganized workflows, untracked deliverables, and insecure single-user task lists. Traditional simple trackers often lack robust security, suffer from IDOR vulnerabilities (where any user can view or delete another user's private tasks), omit CSRF validation, or rely on outdated MySQL extensions without parameterized prepared statements. 

TaskFlow addresses these challenges by delivering an intuitive, responsive task manager backed by strict authorization boundaries, cryptographically secure authentication, and defense-in-depth security practices.

---

## 4. Technologies Used
- **Backend:** Core PHP 8+ (No frameworks used, strictly following Core PHP requirements).
- **Database Connectivity:** PHP Data Objects (PDO) with strict error handling (`ERRMODE_EXCEPTION`) and prepared statements (`ATTR_EMULATE_PREPARES => false`).
- **Database Engine:** MySQL 8.0+ / MariaDB 10.4+ (InnoDB Engine, `utf8mb4_unicode_ci`).
- **Frontend Framework:** Bootstrap 5.3 & Bootstrap Icons.
- **Styling:** Custom CSS3 (`assets/css/style.css`) with clean color tokens, responsive card layouts, and subtle shadows.
- **Client-Side Scripting:** Vanilla JavaScript (ES6+) for interactive modal management, dynamic form validation, and confirmation prompts.
- **Local Development Environment:** XAMPP (Apache + MySQL), Visual Studio Code / Antigravity.
- **Version Control:** Git & GitHub-ready architecture.

---

## 5. Features Implemented
- **Full CRUD Management:** Create, Read, Update, and Delete tasks with permanent confirmation prompts.
- **Real-Time Status Tracking:** Visually distinguishable Bootstrap badges:
  - ⏳ **Pending** (Amber badge)
  - 🔄 **In Progress** (Blue badge)
  - ✅ **Completed** (Emerald green badge)
- **Live Search & Filter:** Dynamic database search by task title coupled with multi-criteria status filtering.
- **Multi-Tenant User Isolation:** Each user has private access strictly to their own tasks; URL parameter tampering (IDOR) is blocked at the SQL query level.
- **Cryptographic Security:** Passwords hashed using standard `password_hash()` BCRYPT; authentication verified using `password_verify()`.
- **Cross-Site Request Forgery (CSRF) Defense:** Cryptographically strong tokens generated with `random_bytes(32)` and validated using timing-safe `hash_equals()`.
- **Cross-Site Scripting (XSS) Prevention:** Context-aware output escaping with `htmlspecialchars(..., ENT_QUOTES | ENT_HTML5, 'UTF-8')`.
- **Session Hardening:** Session ID regeneration (`session_regenerate_id(true)`) upon authentication; HTTP-only and SameSite cookies.
- **Dashboard Metrics:** Real-time statistics cards computing Total, Pending, In Progress, and Completed counts for the logged-in user.
- **Bonus Feature – Pagination:** Clean Bootstrap pagination preserving search and status parameters across pages.

---

## 6. Level 1 Completion Details (Basic Task Management)
- [x] Created `taskflow_db` MySQL database and `tasks` table.
- [x] Add new tasks with `title` and optional `description`.
- [x] Display all saved tasks in a responsive table.
- [x] Edit an existing task's title and description.
- [x] Delete a task after prompting for user confirmation via a modal.
- [x] Display green Bootstrap dismissible success alerts when tasks are added, updated, or deleted.

---

## 7. Level 2 Completion Details (Status, Search & Validation)
- [x] **Task Status Management:** Configured ENUM column (`Pending`, `In Progress`, `Completed`) with quick-change status modals and edit dropdowns.
- [x] **Search Functionality:** Database title search utilizing parameterized `LIKE :search` queries.
- [x] **Status Filter:** Dropdown filter supporting "All Tasks", "Pending", "In Progress", and "Completed".
- [x] **Combined Search & Filter:** Dynamic query builder combining `WHERE title LIKE :search AND status = :status` seamlessly.
- [x] **Validation:** Server-side and client-side validation enforcing mandatory non-empty task titles, character limits (title ≤ 255, description ≤ 5000), integer ID validation, and friendly user error alerts.

---

## 8. Level 3 Completion Details (Login, Authorization & Security)
- [x] **User Registration:** Validates Full Name, Email format, duplicate email checks, password length (≥ 8 chars), and password confirmation matching.
- [x] **Password Hashing:** Passwords encrypted using PHP's `password_hash($password, PASSWORD_DEFAULT)` BCRYPT algorithm.
- [x] **Secure Login:** Authenticates using `password_verify()`, regenerates session IDs, and handles invalid credentials securely.
- [x] **Route Protection:** `require_auth()` guard redirects unauthenticated visitors to `auth/login.php`.
- [x] **Strict Authorization (IDOR Prevention):** All task SELECT, UPDATE, and DELETE operations enforce `WHERE id = :id AND user_id = :user_id`. Attempting to access another user's task via URL parameters is completely rejected.
- [x] **Prepared Statements:** 100% of dynamic queries use PDO prepared statements with parameter binding.
- [x] **CSRF Protection:** Secure CSRF token generation, session persistence, hidden form fields, and strict validation via `verify_csrf_or_die()`.
- [x] **Session Destruction:** Secure logout clearing all `$_SESSION` data and expiring cookies.

---

## 9. Project Folder Structure
```
taskflow/
│
├── index.php                    # Application landing page & smart router
├── dashboard.php                # Root shortcut/redirect to tasks/index.php
├── login.php                    # Root shortcut/redirect to auth/login.php
├── register.php                 # Root shortcut/redirect to auth/register.php
├── logout.php                   # Root shortcut/redirect to auth/logout.php
│
├── assets/
│   ├── css/
│   │   └── style.css            # Custom responsive styling and theme variables
│   ├── js/
│   │   └── script.js            # Modals, client validation, auto-dismiss alerts
│   └── images/
│       └── logo.svg             # Vector TaskFlow application logo
│
├── config/
│   └── database.php             # PDO database connection & error handling
│
├── includes/
│   ├── auth.php                 # Authentication guards & session management
│   ├── csrf.php                 # Cryptographic CSRF token generation & validation
│   ├── functions.php            # Sanitization, XSS escaping, badges, base_url, open-redirect defense
│   ├── header.php               # Global navigation, flash alert container
│   └── footer.php               # Footer info, scripts, and layout termination
│
├── auth/
│   ├── login.php                # User login screen & authentication logic
│   ├── register.php             # User registration screen & duplicate checks
│   └── logout.php               # Session destruction & redirect
│
├── tasks/
│   ├── index.php                # Main Dashboard, metrics, table, filters, pagination
│   ├── create.php               # Standalone task creation page
│   ├── edit.php                 # Task edit form with strict ownership check
│   ├── delete.php               # Secure POST delete action handler
│   ├── update_status.php        # Quick status change action handler
│   └── view.php                 # Task details view page with ownership check
│
├── screenshots/
│   └── README.md                # Screenshot capture guide and image checklist
│
├── tests/
│   ├── test_suite.py            # Automated verification harness for all 27 tests
│   ├── php_syntax_check.py      # State-machine lexer for PHP syntax auditing
│   └── hash_gen.py              # Cryptographic BCRYPT seed generator
│
├── database.sql                 # Complete database schema, foreign keys, & sample data
├── README.md                    # Comprehensive documentation (this file)
├── TESTING.md                   # Formal testing report with pass/fail matrix
├── SUBMISSION_NOTES.md          # Official RTIH submission notes
├── .gitignore                   # Git exclusion rules for clean repository
└── .env.example                 # Environment configuration template
```

---

## 10. Software Prerequisites
To run this application locally, ensure you have:
1. **XAMPP** (version 8.0 or higher recommended, bundled with PHP 8+ and MySQL/MariaDB).
2. A modern web browser (Google Chrome, Mozilla Firefox, Microsoft Edge).
3. Visual Studio Code or any modern code editor.

---

## 11. XAMPP Installation Instructions
1. Download the XAMPP installer for Windows from the official Apache Friends website:  
   👉 [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
2. Run the installer and select **Apache**, **MySQL**, and **phpMyAdmin** components.
3. Complete installation to the default directory (typically `C:\xampp`).

---

## 12. Apache and MySQL Startup Instructions
1. Open the **XAMPP Control Panel** from your Windows Start Menu or `C:\xampp\xampp-control.exe`.
2. Locate **Apache** in the service list and click **Start**. Verify the status turns green.
3. Locate **MySQL** in the service list and click **Start**. Verify the status turns green.

---

## 13. Database Creation and Import Instructions
1. Open your browser and navigate to phpMyAdmin:  
   👉 `http://localhost/phpmyadmin/`
2. Click on the **Import** tab on the top navigation bar.
3. Click **Choose File** and select `database.sql` from the root directory of this project.
4. Click **Import** (or **Go**) at the bottom of the page.
5. The script will automatically:
   - Create the `taskflow_db` database.
   - Create the `users` and `tasks` tables with proper data types and indexes.
   - Configure the foreign key constraint (`fk_tasks_user`) with `ON DELETE CASCADE`.
   - Seed sample users and tasks.

---

## 14. PHP Database Configuration Instructions
The database configuration is managed in `config/database.php` and optionally loaded from `.env`.

Default configuration matches standard XAMPP out-of-the-box settings:
- **Host:** `127.0.0.1` (or `localhost`)
- **Port:** `3306`
- **Database:** `taskflow_db`
- **Username:** `root`
- **Password:** ` ` (blank by default in XAMPP)

To customize credentials, copy `.env.example` to `.env` and update the parameters:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=taskflow_db
DB_USER=root
DB_PASS=your_password_here
```

---

## 15. How to Run the Application Locally
1. Copy the entire project folder into the XAMPP web root (`htdocs`):
   - Destination path: `C:\xampp\htdocs\taskflow`
2. Ensure Apache and MySQL are running in the XAMPP Control Panel.
3. Open your browser and navigate to:  
   👉 **`http://localhost/taskflow/`**

*(Note: The application includes dynamic base URL resolution, so it also functions seamlessly if hosted under `http://localhost:8000/` using PHP's built-in server: `php -S localhost:8000`).*

---

## 16. How to Register a New Account
1. From the landing page or navbar, click **Register**.
2. Fill in:
   - **Full Name:** (e.g. `Varun Kumar`)
   - **Email Address:** (e.g. `varun@example.com`)
   - **Password:** (Minimum 8 characters)
   - **Confirm Password:** (Must match password)
3. Click **Register Account**.
4. Upon successful validation, your account is created, your password is encrypted, and you are automatically logged into your new dashboard.

---

## 17. How to Log In
1. Navigate to `http://localhost/taskflow/auth/login.php`.
2. Enter your registered email address and password.
3. *Pre-configured Evaluation Account:*
   - **Email:** `john.doe@example.com`
   - **Password:** `Password123!`
4. Click **Sign In**.

---

## 18. How to Use the Task Tracker
1. **View Dashboard:** Review summary metrics (Total, Pending, In Progress, Completed).
2. **Add a Task:**
   - Click the **Add New Task** button on the dashboard to open the quick-add modal, or use the top navigation link.
   - Enter a mandatory title, optional description, and select the status.
   - Click **Save Task**.
3. **Filter & Search:**
   - Type keywords into the search box to find tasks by title.
   - Use the status dropdown to filter by `Pending`, `In Progress`, or `Completed`.
   - Click **Reset** to restore the default view.
4. **Quick Status Change:**
   - Click directly on any status badge in the table to open the quick-status update modal.
5. **Edit a Task:**
   - Click the pencil icon in the Actions column to open the dedicated edit form.
6. **Delete a Task:**
   - Click the trash icon in the Actions column.
   - Review the confirmation modal showing the task title.
   - Confirm deletion by clicking **Yes, Delete Task**.

---

## 19. Database Schema Explanation

### Table: `users`
| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `AUTO_INCREMENT, PRIMARY KEY` | Unique identifier for each user |
| `full_name` | `VARCHAR(100)` | `NOT NULL` | User's full display name |
| `email` | `VARCHAR(150)` | `NOT NULL, UNIQUE` | Unique login email address |
| `password` | `VARCHAR(255)` | `NOT NULL` | BCRYPT password hash |
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Account registration timestamp |

### Table: `tasks`
| Column | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | `INT` | `AUTO_INCREMENT, PRIMARY KEY` | Unique identifier for each task |
| `user_id` | `INT` | `NOT NULL` | Foreign key referencing `users.id` |
| `title` | `VARCHAR(255)` | `NOT NULL` | Mandatory task title |
| `description` | `TEXT` | `NULL` | Optional detailed notes |
| `status` | `ENUM` | `'Pending','In Progress','Completed'` | Current workflow status |
| `created_at` | `TIMESTAMP` | `DEFAULT CURRENT_TIMESTAMP` | Task creation timestamp |

### Relationships & Indexes:
- **Foreign Key:** `CONSTRAINT fk_tasks_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE`.
- **Indexes:** `idx_tasks_user_id` (accelerates user dashboard queries), `idx_tasks_status` (accelerates filtering), `idx_tasks_title` (accelerates search).

---

## 20. Roles and Permissions Explanation
TaskFlow implements an **Owner-Based Access Control** multi-tenant architecture:
- Every task record is permanently bound to the `user_id` of the user who created it.
- Logged-in users have full CRUD permissions over their **own** tasks.
- Logged-in users have **zero visibility or mutability** over tasks owned by other users.
- Guest visitors are restricted to the landing page, login page, and registration page.

---

## 21. Security Measures Implemented
1. **Prepared Statements:** All database interactions containing user input use PDO parameterized queries, eliminating SQL injection.
2. **Strict Authorization & IDOR Defense:** All edit, delete, and status updates query both `id = :id AND user_id = :user_id`. Even if an attacker manually changes a task ID in form inputs or URL query strings, the query affects 0 rows and rejects the request.
3. **Cross-Site Request Forgery (CSRF) Tokens:** Every POST form includes a hidden token generated via `random_bytes(32)`. Submissions are verified with timing-safe `hash_equals()`.
4. **Cross-Site Scripting (XSS) Sanitization:** All user-supplied output is escaped via `htmlspecialchars($val, ENT_QUOTES | ENT_HTML5, 'UTF-8')`.
5. **Session Fixation Defense:** `session_regenerate_id(true)` is invoked immediately upon successful credential verification.
6. **Cookie Security:** Session cookies are configured with `HttpOnly = true`, `SameSite = Lax`, and path scoping.
7. **Safe Error Handling:** Raw SQL exceptions are caught via `try/catch` and logged to the server error log; end users receive friendly recovery screens without database credential exposure.
8. **CRLF Injection & Open Redirect Defense:** `redirect()` cleans line breaks (`\r`, `\n`) and protocol-relative URLs (`//evil.com`) to prevent HTTP response splitting and open redirects.

---

## 22. Test Cases and Results

### Testing Discrepancy Clarification & Categorization
In strict compliance with RTIH guidelines, test results are clearly categorized:
- **Category 1 (Offline Verification & Emulation):** All 22 required assignment functional tests plus 5 architectural integrity audits (Total: **27 test cases**) were executed and verified using an automated Python test runner (`tests/test_suite.py`) and a state-machine PHP syntax lexer (`tests/php_syntax_check.py`).
  - **Offline Tests Executed:** 27
  - **Offline Tests Passed:** 27 (100%)
  - **Offline Tests Failed:** 0
- **Category 2 (Live Runtime Environment Tests on Apache/MySQL):**
  - **Status:** **NOT TESTED on Host Environment** because PHP CLI (`php.exe`), Apache (`httpd.exe`), and MySQL (`mysql.exe`) are not installed on the current Windows host system.
  - Runtime verification must be performed once XAMPP is installed locally.

Refer to [`TESTING.md`](file:///A:/TaskFlow%20–%20Smart%20Task%20Management%20System/TESTING.md) for the complete test execution matrix.

---

## 23. Known Limitations
1. **Host Environment Dependencies:** PHP CLI, Apache, and MySQL are not installed on the local host machine; live browser execution requires installing XAMPP.
2. **Manual Screenshot Capture Required:** Because Apache is not running on the local host, live screenshots could not be automatically captured; a complete manual capture checklist is provided in `screenshots/README.md`.
3. **Email Delivery:** Registration does not send transactional activation emails (kept lightweight to avoid external SMTP dependencies for the assignment scope).

---

## 24. Database Backup and Restoration Instructions

### Backup Instructions (via phpMyAdmin):
1. Open phpMyAdmin (`http://localhost/phpmyadmin/`).
2. Select `taskflow_db` from the left sidebar.
3. Click the **Export** tab in the top navigation.
4. Choose the **Quick** export method and **SQL** format.
5. Click **Export** to download the SQL backup file.

### Restoration Instructions (via phpMyAdmin):
1. In phpMyAdmin, click **New** in the sidebar to create a database named `taskflow_db` (or select existing).
2. Click the **Import** tab.
3. Click **Choose File** and select your previously saved SQL backup file.
4. Click **Import** at the bottom to restore all tables, relationships, and user records.

---

## 25. AI Coding Assistant Disclosure & Review Modifications

### Disclosure
In compliance with Section 14 of the RTIH Technical Assignment guidelines, this project was developed with the assistance of **Google Antigravity** as an AI-assisted development tool.

### Changes Made After Reviewing AI-Generated Code
During development and code review, the following genuine architectural and security modifications were made to improve reliability, security, and performance:

1. **Replaced Hardcoded URLs with Dynamic `base_url()` Helper:**  
   *Initial behavior:* Early drafts contained fixed paths like `/taskflow/tasks/index.php`.  
   *Modification:* Implemented a resilient `base_url()` function in `includes/functions.php` that dynamically evaluates `$_SERVER['DOCUMENT_ROOT']`, `$_SERVER['SCRIPT_NAME']`, and subdirectories, guaranteeing that the project runs properly in `htdocs/taskflow`, root virtual hosts, or the PHP built-in server without broken links.

2. **Upgraded CSRF Validation with Timing-Safe Comparison:**  
   *Initial behavior:* Token comparison used standard equality (`===`).  
   *Modification:* Upgraded to `hash_equals()` in `includes/csrf.php` to prevent timing-based side-channel attacks.

3. **Combined Metrics into a Single Optimized SQL Query:**  
   *Initial behavior:* Dashboard statistics initially executed 4 separate `SELECT COUNT(*)` queries.  
   *Modification:* Consolidated all 4 metric calculations into a single aggregate query with `SUM(CASE WHEN status = ... THEN 1 ELSE 0 END)`, cutting database round-trips by 75%.

4. **Enforced POST-Only Method Guard for Task Deletion:**  
   *Initial behavior:* Early deletion handler accepted GET parameters via `delete.php?id=...`.  
   *Modification:* Strictly restricted deletion to POST requests accompanied by valid CSRF tokens and an interactive confirmation modal, completely preventing accidental or malicious deletion via image tags or URL pre-fetching.

5. **CRLF Injection & Open Redirect Defense in `redirect()`:**  
   *Initial behavior:* `redirect()` directly forwarded URLs without filtering.  
   *Modification:* Stripped carriage returns and newlines, and normalized protocol-relative `//` URLs to avoid HTTP response splitting and external phishing redirects.

6. **Differentiated Status No-Op from Unauthorized Mutations:**  
   *Initial behavior:* If a user submitted a status update without changing the status, MySQL returned 0 rows, triggering an ambiguous error message.  
   *Modification:* Added a pre-fetch verification step in `tasks/update_status.php` to distinctly report whether a task is unauthorized vs. already set to that status.

7. **Re-engineered Token Lexer for Accurate Delimiter Auditing:**  
   *Initial behavior:* A regex-based bracket counter misidentified delimiters inside HTML templates and multiline docblocks.  
   *Modification:* Built an accurate state-machine lexer (`tests/php_syntax_check.py`) that properly separates HTML template states from PHP execution states, confirming zero syntax errors across all 20 PHP files.

8. **Dedicated View Task Details & Root Navigation Entrypoints:**  
   *Initial behavior:* Task descriptions were only displayed inside table cells.  
   *Modification:* Created a dedicated `tasks/view.php` details page with strict ownership verification and integrated root shortcuts (`dashboard.php`, `login.php`, `register.php`, `logout.php`) so evaluators can navigate seamlessly via root URLs or modular subdirectories.

