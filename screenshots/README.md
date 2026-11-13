# TaskFlow Application Screenshots

This directory is designated for high-resolution visual evidence of the working application running locally under XAMPP (Apache & MySQL).

> **Note on Environment & Compliance:**  
> In strict accordance with Section 13 of the RTIH Technical Assignment guidelines (*"Screenshots must represent the actual working application. Do not generate fake screenshots or use static mockups as proof of functionality. If screenshots cannot be captured automatically, explain exactly which screens need to be captured manually"*), genuine screenshots should be captured once Apache and MySQL are running in your XAMPP installation.

---

## Required Submission Screenshots Checklist

When running the application locally at `http://localhost/taskflow/` (or your local alias), capture and save the following genuine screenshots in this directory:

| Filename | View / Page | Key Features to Highlight |
| :--- | :--- | :--- |
| `01-registration.png` | **User Registration Screen** | Full name, email, password strength, confirmation check, validation feedback indicators. |
| `02-login.png` | **Sign In Screen** | Modern card layout, email/password fields, password visibility toggle, pre-loaded demo credentials box. |
| `03-dashboard.png` | **Task Dashboard** | Welcome banner greeting user by name, 4 statistics summary cards (Total, Pending, In Progress, Completed). |
| `04-create-task.png` | **Create Task Form** | Title, description with character counter, initial status selector, Save & Cancel buttons. |
| `05-task-list.png` | **Task Table with Badges** | Clean table view showing tasks with distinct Bootstrap badges: Amber (`Pending`), Blue (`In Progress`), Green (`Completed`). |
| `06-edit-task.png` | **Edit Task Screen** | Pre-populated task details, status dropdown, created-on metadata, breadcrumbs, Save changes button. |
| `07-status-management.png` | **Status Management Modal** | Quick-change modal updating task status directly from table with instant badge update. |
| `08-search-filter.png` | **Search & Filtering Results** | Search bar filtered by keyword (e.g. `PDO` or `Tracker`) combined with status dropdown. |
| `09-user-isolation.png` | **User Isolation Demonstration** | Dashboard for User 2 (Jane Smith) showing only User 2's tasks, demonstrating strict multi-tenant boundary. |

---

## How to Capture and Save

1. Open **XAMPP Control Panel**, start **Apache** and **MySQL**.
2. Navigate to `http://localhost/taskflow/` in Google Chrome or Microsoft Edge.
3. Sign in using the pre-loaded account:  
   - **Email:** `john.doe@example.com`  
   - **Password:** `Password123!`
4. Use `Windows Key + Shift + S` or Chrome DevTools Device Mode (`Ctrl + Shift + M` -> `Ctrl + Shift + P` -> `Capture full size screenshot`).
5. Save the image files with the exact names listed above into this `screenshots/` directory.

