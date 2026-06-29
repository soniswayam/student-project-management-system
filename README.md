# Student Project Submission System

A Laravel 13 web application for managing college project submissions across three roles —
**Admin**, **Faculty**, and **Student** — covering the full lifecycle from synopsis to final grading.

Built with **Laravel + Blade + MySQL + Bootstrap 5 + JavaScript**.

---

## Features

### Roles
- **Admin** — manage students, faculty, departments; view all projects; assign one faculty per project; view reports & dashboards.
- **Faculty** — see only assigned projects; review synopsis (approve / reject / request correction); add comments; review final submission; award marks & final remarks.
- **Student** — submit synopsis first; track status; upload final files only after synopsis approval; view faculty comments.

### Project workflow
1. **Synopsis Under Review** — student submits synopsis (this creates the project).
2. **Synopsis Approved** / **Correction Required** — faculty reviews.
3. **Final Submitted** — student uploads report, source zip, PPT, screenshots.
4. **Final Reviewed** → **Completed** — faculty grades the project.

### Business rules
- A student can create **only one** project.
- Project type is **Single** or **Group**.
- Group size is **max 2** students.
- Partner is required only for group projects; leader ≠ partner; a student cannot join two projects.
- Final files can be uploaded **only after** the synopsis is approved.

---

## Tech & structure

| Layer | Location |
|-------|----------|
| Migrations | `database/migrations/` |
| Models | `app/Models/` |
| Controllers | `app/Http/Controllers/{Admin,Faculty,Student,Auth}/` |
| Role middleware | `app/Http/Middleware/RoleMiddleware.php` (alias `role`) |
| Routes | `routes/web.php` |
| Views | `resources/views/` (layouts, partials, admin, faculty, student, auth) |
| Seeder | `database/seeders/DatabaseSeeder.php` |
| Tests | `tests/Feature/ProjectWorkflowTest.php` |

Database tables: `users`, `students`, `faculties`, `departments`, `projects`,
`project_members`, `faculty_assignments`, `project_submissions`, `project_reviews`, `notifications`.

---

## Setup

The project is already configured for the local WAMP MySQL server.

```bash
# 1. Database connection is in .env (MySQL: student_project_system, root / no password)

# 2. Run migrations + seed demo data
php artisan migrate:fresh --seed

# 3. Link storage for uploaded files
php artisan storage:link

# 4. Serve the app
php artisan serve
```

> On Windows, large multipart file uploads can fail under `php artisan serve`
> (single-threaded built-in server). Run the app under **WAMP/Apache**
> (`http://localhost/student-project-management-system/public`) for full upload support.

### Seeded demo accounts (password: `password`)

| Role | Email |
|------|-------|
| Admin | `admin@spss.test` |
| Faculty | `faculty@spss.test` |
| Student | `rahul@spss.test`, `priya@spss.test`, `aman@spss.test` |

New students can also self-register at `/register`.

---

## Tests

```bash
php artisan test --filter=ProjectWorkflowTest
```

Covers synopsis submission, group/partner validation rules, role middleware,
and the full assign → review → upload → grade flow.
