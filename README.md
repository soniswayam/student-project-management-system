# Student Project Submission System

A **Laravel 13** web application for managing college project submissions across three roles —
**Admin / Staff**, **Faculty**, and **Student** — covering the full lifecycle from synopsis to
final grading, plus a lightweight **assignments (homework)** module, role-based access control,
and PDF / Excel exports.

Built with **Laravel 13.8 (PHP 8.3) + Blade + MySQL + Bootstrap 5.3 + vanilla JavaScript**,
**dompdf** for PDF generation, **PhpSpreadsheet** for Excel exports, and **smalot/pdfparser**
for the offline plagiarism check.

> There is **no front-end build step**. Styling is a token-driven stylesheet at
> `public/assets/css/theme.css` layered over the Bootstrap 5.3 CDN bundle, with shared behaviour
> in `public/assets/js/app.js`. No Node, no Vite, no Tailwind, no `npm run build`.

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

### Who creates a project
A project is created **only by the student**, by submitting a synopsis — that is what brings the
row into existence. Staff then assign exactly one guide to it.

```
Student submits synopsis → project created (Synopsis Under Review)
        ↓
Admin assigns a faculty guide  (POST admin/projects/{project}/assign, perm projects.assign)
        ↓
Guide is notified; the project appears in their list
```

Neither admin nor faculty can create a project — there is no create/store route under
`admin/projects`. Faculty review and grade what admin assigns them. This is unlike
**assignments (homework)**, which faculty create directly for a whole department with no
assign step.

### Business rules
- A student can create **only one** project.
- Project type is **Single** or **Group**.
- Group size is **max 2** students.
- A partner is required only for group projects; the leader ≠ the partner; a student cannot join
  two projects; **the partner must be from the same department** (enforced server-side, not just
  in the picker).
- Final files can be uploaded **only after** the synopsis is approved.
- Exactly **one faculty** is assigned per project.
- **Only the project leader** may edit the synopsis, and only while it is in
  `Correction Required` or `Rejected`. Resubmitting returns it to `Synopsis Under Review` and
  increments `projects.revision`.
- Students **cannot change their own semester** — it is admin-managed (student edit form and the
  bulk promote action). Anything posted for it from the profile form is ignored.
- **Marks are optional.** When faculty leaves them blank, the marks row is omitted entirely from
  the project page, the report PDF and the certificate.

### Deadlines & late submissions
Projects and assignments both close in three phases:

```
....... open ....... | ..... grace (late but accepted) ..... | closed
                    due                              due + grace
```

- A project's deadline resolves as **`projects.deadline_override` → `departments.submission_deadline` → none**.
  The override is a per-project extension set by admin (with an optional reason); clearing it puts
  the project back on its department date. No date anywhere = always open.
- Assignments use their existing `assignments.due_date`.
- Grace defaults: **12 hours for projects, 6 for assignments**, both editable in admin **Settings**.
  Set 0 to close exactly on the due date.
- Submitting inside the grace window is accepted and flagged **Late**; `is_late` and
  `late_by_minutes` are stamped at submit time, so a later extension never rewrites history.
- After the grace window the form is hidden **and** the POST is refused server-side.
- Late submissions show a badge to the student, the guide and admin, and the guide's notification
  says so explicitly.

### Assignments (homework) module
- **Faculty** create assignments for a department, with an optional attachment (question paper,
  etc.) and a due date.
- **Students** in that department see the assignments and submit their work.
- **Faculty** review and mark submissions as checked.
- **Admin/Staff** get a read-only view plus distribution reports (Excel / PDF).

### Degree levels (Graduation / Post-Graduation)
Departments are the courses, so the level and its length live on the department:

| Course | Level | Years | Semesters |
|---|---|---|---|
| B.C.A / B.COM / B.B.A | Graduation | 3 | 6 |
| MSC (IT & CA) | Post-Graduation | 2 | 4 |

- `departments.level` + `departments.duration_years`. `total_semesters` fills in as **years × 2**
  when left blank, and an explicitly set value is never overwritten (a 5-year integrated course can
  have 9). Both are **required** when adding a course — one with no length has no semester cap.
- **The promotion cap now fires.** `promoteSemester` always checked the final semester, but
  `total_semesters` was null on every course, so the check never bit and students could be advanced
  past the end of their degree. Deriving it from the duration switches that existing guard on.
- **Semester Review** — an admin screen listing anyone sitting beyond their course's last semester,
  with a per-row and "fix all" action. It only ever moves a student *back*, re-checks each row at
  submit time rather than trusting the posted list, and changes nothing until you click. The link
  appears on the Students page only when there is something to review.
- **UG/PG badges and filters** on the students, projects and departments lists, plus a
  Graduation / Post-Graduation split on the admin dashboard.

#### Alumni — after the final semester
- **Passing out is always an explicit admin action**, never automatic. Select students on the
  Students list and use **Move to Alumni**; the batch is the current academic year, so there is no
  picker there. Choosing a batch is an Alumni-page concern, where it filters the register.
- The confirm names what is unusual about the selection — how many have not reached their final
  semester, and how many have no completed project — and lets you go ahead anyway. Neither blocks:
  on a typical install most final-semester students have no completed project, so a hard rule would
  block nearly everyone.
- **Alumni page** with a **batch dropdown**, plus course, level and free-text filters, and an Excel
  export of whatever is filtered. Alumni leave the Students list, and are still reachable there by
  filtering status = Alumni.
- **Alumni keep read-only access.** `users.status = graduated` is deliberately *not* an inactive
  state — refusing the login would take the completion certificate away with it. They can sign in
  and view their project, reviews and certificate; every write route is closed by
  `BlockAlumniWritesMiddleware` (alias `not-alumni`).
- Reversible: **Restore to studying** clears the batch stamp and puts the account back to active.

#### Project rules that differ by level
Two rows in `degree_level_settings`, edited in admin **Settings**:

| Rule | Meaning |
|---|---|
| `max_group_size` | 1 makes projects individual-only at that level — enforced server-side, and the group option is not offered in the form |
| `submission_deadline` | Level default, used only when the department has no date of its own: **project override → department → level → none** |
| `max_marks` | **Nullable.** Blank means the level is *not marked* — no marks field for faculty, and nothing about marks on the project page, report PDF or certificate |
| `viva_required` | Flags projects with no viva scheduled; never blocks anything |

Marks stay optional throughout, and `Project::marksLabel()` is the single place that decides
whether anything renders. It returns null both when no mark was awarded and when the level has no
scheme — a bare `/100` is never printed in either case.

### Academic years & archive
- **Academic years** (e.g. `2026-27`) with exactly one marked **current**; new projects and
  assignments are stamped with it automatically. A null year means "not filed under a session"
  and always stays visible.
- **Archiving** a year is a reversible flag (`archived_at`) — nothing is moved or deleted. Its
  projects drop out of the active admin/faculty lists and appear in a read-only **Archive**
  browser with its own filters and Excel export.
- The current year cannot be archived (nominate a successor first), and a year with work filed
  under it cannot be deleted.

### Plagiarism & duplicate detection
Runs entirely offline against the college's own corpus — no third-party API, so it works on a
localhost install. It can tell you a submission resembles **another submission in this system**;
it cannot check the open web.

- `App\Support\SimilarityService` — normalise → stopword-strip → 3-word shingles → Jaccard plus
  containment (so a short abstract pasted into a long one still scores). Reports the overlapping
  phrases as evidence.
- Checked at **synopsis submit** (title + abstract) and at **final submit** (extracted report text
  via `smalot/pdfparser` for PDF, `ZipArchive` for DOCX; cached in `project_submissions.report_text`).
- **Archived years are included** — last year's work is exactly what gets resubmitted.
- Two admin-editable thresholds: **warn** (default 60%) flags it to the student and guide;
  **block** (default 85%) refuses the synopsis server-side. Set block to 100 to never block.
- Admin **Similarity Report** with a side-by-side comparison view.

### Viva schedule (optional)
- Created and edited by the project's **assigned guide**; admin and students see it read-only.
  One owner per record, so two people cannot overwrite each other's slot.
- Date/time, in-person venue or online meeting link, notes, and an examining panel (the guide is
  always on it). Scheduling, moving or cancelling notifies the students and every panelist.
- Entirely optional — no viva row means no viva card renders anywhere.

### Re-upload requests
Previously only the synopsis could be sent back. A guide can now ask for a new version of the
**final project** or an **assignment submission** too.

- An **open request reopens the upload form for that one student**, past deadline or not — checked
  server-side beside the normal window check, so the bypass is explicit and scoped.
- A re-upload the guide asked for is **not** flagged late.
- Auto-resolves when the student uploads; the guide is re-notified.

### Faculty workload & replacement
- **Workload** screen: projects guided, synopsis/final reviews pending, assignments set,
  submissions unchecked, plus an optional per-faculty capacity (`faculties.max_projects`).
  Closed academic years do not count against anyone.
- **Replacing a guide** writes a `faculty_assignment_histories` row and notifies the incoming
  guide, the **outgoing** guide, and the students. (Assignment previously overwrote the row
  silently, so the removed guide simply found the project gone.)
- **Bulk assignment** — checkbox + bulk bar on the projects list assigns one guide to many
  projects, skipping ones they already guide and warning (never blocking) past capacity.

### Deadline reminders
- `php artisan reminders:send` — scheduled daily at 07:00 in `routes/console.php`.
- Students N days before and on a project/assignment due date; guides about reviews left waiting;
  everyone the day before a viva.
- `reminder_logs` has a unique index on (user, thing, kind, date), so re-running the command the
  same day sends nothing twice. `--dry-run` lists without sending.

### Account approvals
- Self-registered students land in `pending` and cannot sign in until approved.
- Admin **Approvals** queue with per-row approve, **bulk approve**, and reject-with-reason.
  Rejected accounts are blocked rather than deleted, so the roll number stays claimed and the
  decision stays on record. Both outcomes notify the student in-app and by email.

### Reporting & documents
- Admin **reports** dashboard with Excel export.
- **Project reports**, **synopsis**, and **completion certificates** exported as PDF (dompdf).
- **Excel** exports for students, projects, assignment distribution, archive, similarity and
  workload (PhpSpreadsheet). Filtered lists export **the filtered set**.
- **Advanced filters** on the projects list — department, academic year, guide (including
  "unassigned"), status, similarity-flagged / late / re-upload-pending / viva, created-date range,
  and free text across project name, student name and roll number.
- **File preview** — PDFs and images open in a full-screen viewer; formats a browser cannot render
  (Office, ZIP) get download only rather than a preview that would fail.
- In-app **notifications** shared across all roles; `Notification::notify()` sends in-app + email.
- **Password reset by email** with a single-use 60-minute link. The reply is identical whether or
  not the address exists, so the form cannot be used to discover accounts. With `MAIL_MAILER=log`
  the link is also shown on screen so the offline flow stays testable.
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
| `active` | `App\Http\Middleware\EnsureActiveMiddleware` | pending / blocked accounts cannot use the student area |
| `not-alumni` | `App\Http\Middleware\BlockAlumniWritesMiddleware` | alumni may read their records but not submit |

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
| Reports | `reports.view`, `similarity.view`, `workload.view` |
| Academic Years | `academic_years.manage`, `archive.view` |
| Administration | `admins.manage`, `roles.manage`, `approvals.manage`, `settings.manage` |

### Default grants (from `RoleSeeder`)
- **super_admin** → `["*"]` (everything).
- **admin** → `dashboard.view`, `students.view/create/edit`, `faculty.view`, `departments.view`,
  `projects.view/assign/export`, `assignments.view/manage`, `reports.view`, `similarity.view`,
  `workload.view`, `archive.view`, `approvals.manage`. (`academic_years.manage` is super-admin
  only by default — closing a year affects every list at once.)
- **faculty** / **student** → no admin permissions (they use their own role-scoped areas).

---

## Tech & project structure

| Layer | Location |
|-------|----------|
| Migrations | `database/migrations/` |
| Models | `app/Models/` (21: `User`, `Role`, `Student`, `Faculty`, `Department`, `Project`, `ProjectMember`, `FacultyAssignment`, `ProjectSubmission`, `ProjectReview`, `Assignment`, `AssignmentSubmission`, `CollegeSetting`, `Notification`, `AcademicYear`, `ProjectSimilarity`, `Viva`, `RevisionRequest`, `FacultyAssignmentHistory`, `ReminderLog`) |
| Support | `app/Support/` (`SubmissionWindow`, `SimilarityService`, `DocumentText`, `PlagiarismChecker`, `SpreadsheetExporter`, `SpreadsheetImporter`, `PasswordRules`) |
| Commands | `app/Console/Commands/SendDueDateReminders.php`, scheduled in `routes/console.php` |
| Controllers | `app/Http/Controllers/{Admin,Faculty,Student,Auth}/` + `DashboardController`, `NotificationController` |
| Middleware | `app/Http/Middleware/{RoleMiddleware,StaffMiddleware,PermissionMiddleware}.php` |
| Routes | `routes/web.php` |
| Views | `resources/views/` (`layouts`, `partials`, `admin`, `faculty`, `student`, `auth`) |
| Config | `config/permissions.php` (permission catalog), `config/college.php` (branding) |
| Seeders | `database/seeders/{DatabaseSeeder,RoleSeeder,BulkDemoSeeder}.php` |
| Tests | `tests/Feature/`, `tests/Unit/` |

### Database schema
Application tables:

| Table | Purpose |
|-------|---------|
| `users` | Login accounts; carries the `role` slug |
| `roles` | Role definitions + permissions (JSON), `is_staff`, `is_system` |
| `students` | Student profile (roll no, department, phone, semester) + `passed_out_year_id` / `passed_out_at` for alumni |
| `faculties` | Faculty profile (designation, department, phone) linked to a user |
| `departments` | College courses (e.g. BCA, BBA) + `level`, `duration_years`, `total_semesters`, `submission_deadline` |
| `degree_level_settings` | Two rows — the project rules that differ between Graduation and Post-Graduation |
| `projects` | One project per student leader; type, techs, abstract, status, `revision`, `deadline_override`, marks, remarks |
| `project_members` | Group members mapped to a project |
| `faculty_assignments` | The single faculty assigned to a project (unique per `project_id`) |
| `project_submissions` | Uploaded final files (report, source zip, PPT, screenshots) + `is_late` |
| `project_reviews` | Faculty review history (synopsis & final) |
| `assignments` | Homework created by faculty for a department (attachment, due date) |
| `assignment_submissions` | Student submissions to an assignment + checked status + `is_late` |
| `college_settings` | Single-row institute branding for PDFs, grace hours, duplicate thresholds, reminder lead days |
| `notifications` | In-app notifications per user |
| `academic_years` | Sessions (`2026-27`); one `is_current`, `archived_at` closes it |
| `project_similarities` | Recorded resemblance between two projects (`stage`, `score`, evidence) |
| `vivas` / `viva_panelists` | Optional viva slot per project + its examining panel |
| `revision_requests` | A guide asking a student to upload something again |
| `faculty_assignment_histories` | Who guided a project, and when it changed hands |
| `reminder_logs` | One row per reminder sent — unique per (user, thing, kind, day) |

Plus Laravel framework tables: `migrations`, `sessions`, `cache`, `cache_locks`, `jobs`,
`job_batches`, `failed_jobs`, `password_reset_tokens`.

---

## Requirements

- **PHP** 8.3+ (with `pdo_mysql`, `dom`/`gd` for PDF, `zip` for Excel)
- **Composer** 2.x
- **MySQL** 5.7+ / **MariaDB** 10.4+

Node is **not** required — there is no asset build step.

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

# 3. App key + database
php artisan key:generate
php artisan migrate --seed

# 4. Link storage for uploaded files (required — screenshots and
#    attachments 404 without it)
php artisan storage:link

# 5. Serve the app
php artisan serve
```

**Convenience Composer scripts:**

```bash
composer run setup   # install + .env + key:generate + migrate
composer run dev     # runs php serve + queue listener + pail logs together
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
| `tests/Feature/SynopsisResubmitTest.php` | Edit/resubmit rules, revision counter, leader-only access |
| `tests/Feature/SubmissionDeadlineTest.php` | Grace windows, late flagging, per-project overrides |
| `tests/Feature/PartnerSelectionTest.php` | Partner must come from the student's own department |
| `tests/Feature/AccountFeaturesTest.php` | Profile, semester lock |
| `tests/Feature/StudentManagementTest.php`, `FacultyDepartmentsTest.php` | Admin student & faculty management |
| `tests/Feature/PasswordResetEmailTest.php` | Emailed reset link, single-use + expiring token, no account enumeration, broken mailer |
| `tests/Feature/AcademicYearArchiveTest.php` | Year stamping, archiving hides without deleting, guards, reversibility |
| `tests/Feature/AccountApprovalTest.php` | Pending accounts blocked from login, approve/bulk-approve/reject |
| `tests/Feature/SimilarityDetectionTest.php` | Scoring behaviour, server-side block, archived years included |
| `tests/Feature/WorkflowFeaturesTest.php` | Viva ownership, re-upload bypass scoping, guide replacement, bulk assign, optional marks, reminder dedupe |
| `tests/Feature/AdvancedFilterTest.php` | Project filters and filter-aware export |
| `tests/Feature/NewScreensSmokeTest.php` | Every new screen renders with real data attached |
| `tests/Feature/DegreeLevelTest.php` | Semester derivation and cap, review screen (moves back only), individual-only levels, level deadline precedence, optional per-level marks |
| `tests/Feature/AlumniTest.php` | Batch stamping, register + batch filter, restore, and that an alumnus can sign in and read but cannot submit |

Run a single suite with, e.g. `php artisan test --filter=ProjectWorkflowTest`.

### Scheduled work
Deadline reminders need Laravel's scheduler running:

```bash
php artisan schedule:work                 # local
* * * * * php artisan schedule:run        # server cron
php artisan reminders:send --dry-run      # see what would go out, send nothing
```

---

## License

Released under the **MIT** license.
