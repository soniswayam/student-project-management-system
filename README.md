# Student Project Submission System

A **Laravel 13** web application for managing college project submissions across three roles —
**Admin / Staff**, **Faculty**, and **Student** — covering the full lifecycle from synopsis to
final grading, plus a lightweight **assignments (homework)** module, role-based access control,
and PDF / Excel exports.

Built with **Laravel 13.8 (PHP 8.3) + Blade + MySQL + Bootstrap 5 + JavaScript**, with
**Vite + Tailwind CSS 4** for asset building, **dompdf** for PDF generation, and
**PhpSpreadsheet** for Excel exports.

---

## Features

### Roles & access
- **Super Admin** — full, unrestricted control (all permissions, manages admins, roles, faculty,
  departments, settings). Bypasses every permission check.
- **Admin** — a configurable staff role. By default: students (view/add/edit), faculty (view),
  departments (view), projects (view / assign faculty / export), assignments, and reports — but
  **no** deletes, faculty/department management, or admin/role management.
- **Faculty** — sees only assigned projects; reviews the synopsis (approve / reject / request
  correction), adds comments, reviews the final submission, and awards marks & final remarks.
  Also creates and checks homework **assignments** for their department.
- **Student** — submits a synopsis first, tracks its status, uploads final files only after the
  synopsis is approved, views faculty comments, downloads a completion certificate, and submits
  homework assignments posted for their department.

### Project workflow
The project's `status` moves through these states (see `App\Models\Project` constants):

1. **Synopsis Pending** → **Synopsis Under Review** — the student submits a synopsis (this creates
   the project).
2. **Synopsis Approved** / **Correction Required** / **Rejected** — faculty reviews the synopsis.
3. **Final Submitted** — student uploads the report, source zip, PPT, and screenshots
   (allowed only after synopsis approval).
4. **Final Reviewed** → **Completed** — faculty grades the project (marks + final remarks).

### Business rules
- A student can create **only one** project.
- Project type is **Single** or **Group**.
- Group size is **max 2** students.
- A partner is required only for group projects; the leader ≠ the partner; a student cannot join
  two projects.
- Final files can be uploaded **only after** the synopsis is approved.
- Exactly **one faculty** is assigned per project.

### Assignments (homework) module
- **Faculty** create assignments for a department, with an optional attachment (question paper,
  etc.) and a due date.
- **Students** in that department see the assignments and submit their work.
- **Faculty** review and mark submissions as checked.
- **Admin/Staff** get a read-only view plus distribution reports (Excel / PDF).

### Reporting & documents
- Admin **reports** dashboard with Excel export.
- **Project reports**, **synopsis**, and **completion certificates** exported as PDF (dompdf).
- **Excel** exports for students, projects, and assignment distribution (PhpSpreadsheet).
- In-app **notifications** shared across all roles.
- Editable **college branding** (name, address, affiliation, etc.) shown in PDF headers.

---

## Roles & permissions (RBAC)

Each user has a `role` slug on the `users` table, backed by a row in the `roles` table:

| Column | Meaning |
|--------|---------|
| `name` | Role slug (e.g. `super_admin`, `admin`, `faculty`, `student`) |
| `label` | Human-readable label |
| `permissions` | JSON array of permission keys; `["*"]` = all-access |
| `is_staff` | Whether the role may access the `/admin` area |
| `is_system` | Whether the role is protected (cannot be deleted) |

Access is enforced by three route middleware aliases (registered in `bootstrap/app.php`):

| Alias | Class | Usage |
|-------|-------|-------|
| `role:` | `App\Http\Middleware\RoleMiddleware` | `role:faculty`, `role:student` — exact role match |
| `staff` | `App\Http\Middleware\StaffMiddleware` | gate the whole `/admin` area to staff roles |
| `perm:` | `App\Http\Middleware\PermissionMiddleware` | `perm:students.delete` — require a specific permission |

`User::hasPermission()` returns `true` for any super admin (bypass); otherwise it checks the role's
`permissions` array. Roles and their permissions are editable at runtime via the **Manage Roles**
admin screen.

### Permission catalog
Defined in `config/permissions.php` and grouped for the checkbox UI:

| Group | Keys |
|-------|------|
| Dashboard | `dashboard.view` |
| Students | `students.view`, `students.create`, `students.edit`, `students.delete` |
| Faculty | `faculty.view`, `faculty.manage` |
| Departments | `departments.view`, `departments.manage` |
| Projects | `projects.view`, `projects.assign`, `projects.export` |
| Assignments | `assignments.view`, `assignments.manage` |
| Reports | `reports.view` |
| Administration | `admins.manage`, `roles.manage`, `settings.manage` |

### Default grants (from `RoleSeeder`)
- **super_admin** → `["*"]` (everything).
- **admin** → `dashboard.view`, `students.view/create/edit`, `faculty.view`, `departments.view`,
  `projects.view/assign/export`, `assignments.view/manage`, `reports.view`.
- **faculty** / **student** → no admin permissions (they use their own role-scoped areas).

---

## Tech & project structure

| Layer | Location |
|-------|----------|
| Migrations | `database/migrations/` |
| Models | `app/Models/` (14: `User`, `Role`, `Student`, `Faculty`, `Department`, `Project`, `ProjectMember`, `FacultyAssignment`, `ProjectSubmission`, `ProjectReview`, `Assignment`, `AssignmentSubmission`, `CollegeSetting`, `Notification`) |
| Controllers | `app/Http/Controllers/{Admin,Faculty,Student,Auth}/` + `DashboardController`, `NotificationController` |
| Middleware | `app/Http/Middleware/{RoleMiddleware,StaffMiddleware,PermissionMiddleware}.php` |
| Routes | `routes/web.php` |
| Views | `resources/views/` (`layouts`, `partials`, `admin`, `faculty`, `student`, `auth`) |
| Config | `config/permissions.php` (permission catalog), `config/college.php` (branding) |
| Seeders | `database/seeders/{DatabaseSeeder,RoleSeeder,DemoSeeder}.php` |
| Tests | `tests/Feature/`, `tests/Unit/` |

### Database schema
Application tables:

| Table | Purpose |
|-------|---------|
| `users` | Login accounts; carries the `role` slug |
| `roles` | Role definitions + permissions (JSON), `is_staff`, `is_system` |
| `students` | Student profile (roll no, department, phone) linked to a user |
| `faculties` | Faculty profile (designation, department, phone) linked to a user |
| `departments` | College courses / departments (e.g. BCA, BBA) |
| `projects` | One project per student leader; type, techs, abstract, status, marks, remarks |
| `project_members` | Group members mapped to a project |
| `faculty_assignments` | The single faculty assigned to a project (unique per `project_id`) |
| `project_submissions` | Uploaded final files (report, source zip, PPT, screenshots) |
| `project_reviews` | Faculty review history (synopsis & final) |
| `assignments` | Homework created by faculty for a department (attachment, due date) |
| `assignment_submissions` | Student submissions to an assignment + checked status |
| `college_settings` | Single-row institute branding for PDFs |
| `notifications` | In-app notifications per user |

Plus Laravel framework tables: `migrations`, `sessions`, `cache`, `cache_locks`, `jobs`,
`job_batches`, `failed_jobs`, `password_reset_tokens`.

---

## Requirements

- **PHP** 8.3+ (with `pdo_mysql`, `dom`/`gd` for PDF, `zip` for Excel)
- **Composer** 2.x
- **Node.js** 18+ and npm (Vite 8 + Tailwind CSS 4)
- **MySQL** 5.7+ / **MariaDB** 10.4+

> The `Rejected` status is added via an `ALTER TABLE ... MODIFY ENUM` migration that runs only on
> MySQL/MariaDB. The test suite uses SQLite (`:memory:`), where enum columns are plain strings, so
> that migration is skipped there automatically.

---

## Setup

The project is pre-configured for a local WAMP MySQL server (database `student_submission`,
user `root`, no password — see `.env`).

```bash
# 1. Create the database first if it does not exist:
#    CREATE DATABASE student_submission;

# 2. Install dependencies
composer install
npm install

# 3. App key + database
php artisan key:generate
php artisan migrate --seed

# 4. Link storage for uploaded files
php artisan storage:link

# 5. Build front-end assets
npm run build

# 6. Serve the app
php artisan serve
```

**Convenience Composer scripts:**

```bash
composer run setup   # install + .env + key:generate + migrate + npm install + build
composer run dev     # runs php serve + queue listener + pail logs + vite together
composer run test    # config:clear then php artisan test
```

> **Windows upload note:** large multipart file uploads can fail under `php artisan serve`
> (single-threaded built-in server). For full upload support, run the app under **WAMP/Apache**
> at `http://localhost/student-project-management-system/public`.

### College branding
Institute details shown in PDF headers and certificates live in `config/college.php` and can be
overridden with `.env` keys (`COLLEGE_NAME`, `COLLEGE_TAGLINE`, `COLLEGE_ADDRESS`,
`COLLEGE_AFFILIATION`, `COLLEGE_EMAIL`, `COLLEGE_PHONE`, `COLLEGE_WEBSITE`). They are seeded into
the single `college_settings` row and can also be edited in the admin **Settings** screen.

---

## Seeded demo accounts

All demo accounts use the password **`password`**.

| Role | Email | Access |
|------|-------|--------|
| Super Admin | `superadmin@spss.test` | Full control, incl. faculty, departments, roles, admins, settings |
| Admin | `admin@spss.test` | Students, projects, assign faculty, assignments, reports (no deletes / faculty / dept / admin management) |
| Faculty | `faculty@spss.test` | Assigned projects, reviews, grading, department assignments |
| Student | `rahul@spss.test`, `priya@spss.test`, `aman@spss.test` | Own synopsis, submission, assignments, certificate |

Seeded departments: **BCA**, **MSC (IT & CA)**, **BBA**, **B.Com**.

New students can also self-register at `/register`.

---

## Tests

```bash
php artisan test
```

The suite runs on SQLite in-memory (`phpunit.xml`) and covers:

| Test | Focus |
|------|-------|
| `tests/Feature/ProjectWorkflowTest.php` | Synopsis submission, group/partner rules, assign → review → upload → grade flow |
| `tests/Feature/RoleAccessTest.php` | Role & permission middleware enforcement |
| `tests/Feature/RouteSmokeTest.php` | Key routes respond for each role |
| `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` | Framework smoke tests |

Run a single suite with, e.g. `php artisan test --filter=ProjectWorkflowTest`.

---

## License

Released under the **MIT** license.
