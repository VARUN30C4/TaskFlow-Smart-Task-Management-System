# TaskFlow Application Screenshots

This directory contains high-resolution visual evidence of the working application running locally under XAMPP (Apache 2.4.65 & MariaDB 10.11.16).

> **Verification Status:**  
> All 9 genuine screenshots have been captured directly from the live application running at `http://localhost/taskflow/` via Chrome Headless using the automated Chrome DevTools Protocol engine (`tests/capture_all_screenshots.py`). Each file is verified and ready for evaluation.

---

## Submission Screenshots Gallery

| Filename | View / Page | Key Features Highlighted | Status |
| :--- | :--- | :--- | :---: |
| `01-registration.png` | **User Registration Screen** | Full name, email, password strength, confirmation check, validation feedback indicators. | **CAPTURED & VERIFIED** |
| `02-login.png` | **Sign In Screen** | Modern card layout, email/password fields, password visibility toggle, pre-loaded demo credentials box. | **CAPTURED & VERIFIED** |
| `03-dashboard.png` | **Task Dashboard** | Welcome banner greeting user by name, 4 statistics summary cards (Total, Pending, In Progress, Completed). | **CAPTURED & VERIFIED** |
| `04-create-task.png` | **Create Task Form** | Title, description with character counter, initial status selector, Save & Cancel buttons. | **CAPTURED & VERIFIED** |
| `05-task-list.png` | **Task Table with Badges** | Clean table view showing tasks with distinct Bootstrap badges: Amber (`Pending`), Blue (`In Progress`), Green (`Completed`). | **CAPTURED & VERIFIED** |
| `06-edit-task.png` | **Edit Task Screen** | Pre-populated task details, status dropdown, created-on metadata, breadcrumbs, Save changes button. | **CAPTURED & VERIFIED** |
| `07-status-management.png` | **Status Management Modal** | Quick-change modal updating task status directly from table with instant badge update. | **CAPTURED & VERIFIED** |
| `08-search-filter.png` | **Search & Filtering Results** | Search bar filtered by keyword (`Database`) combined with status dropdown. | **CAPTURED & VERIFIED** |
| `09-user-isolation.png` | **User Isolation Demonstration** | Dashboard for User 2 (Jane Smith) showing only User 2's tasks, demonstrating strict multi-tenant boundary. | **CAPTURED & VERIFIED** |

---

## Automated Capture & Regeneration

To regenerate all screenshots from the live application at any time:
1. Ensure Apache and MySQL are running in XAMPP (`http://localhost/taskflow/`).
2. Run the automated capture script:
   ```powershell
   python tests/capture_all_screenshots.py
   ```
The engine launches Chrome with CDP, sets authenticated session cookies for `john.doe@example.com` and `jane.smith@example.com`, triggers interactive UI elements (such as the status modal), and saves high-resolution PNG images.
