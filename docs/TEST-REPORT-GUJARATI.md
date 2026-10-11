# ટેસ્ટ રિપોર્ટ અને બગ લિસ્ટ — 11-10-2026

## 1. ઉમેરેલો ડેટા

ઉમેરેલા બધા accounts `@bulk.spss.test` વાળા છે. Seeder: `database/seeders/BulkDemoSeeder.php`.

| વસ્તુ | સંખ્યા (આખા DB માં) |
|---|---|
| Students | 701 (500 નવા: BCA / BCOM / BBA / MSCIT, બધા semesters) |
| Faculty | 18 (16 નવા: 8 seeder થી, 8 Admin ફોર્મ થી) |
| Staff | 6 (HOD × 2, Exam Coordinator, Office Clerk + જૂના super admin, admin) |
| નવા roles | HOD, Exam Coordinator, Office Clerk |
| Projects | 182 (70 group / બાકીના single) |
| Assignments | 47 (અડધા due date વટાવી ચૂકેલા, અડધા હજી open) |
| Assignment submissions | 586 (અમુક late, અમુક checked + marks) |
| Viva | 14 (MSc projects) |
| Re-upload requests | 18 |
| Pending account approvals | 8 |
| Blocked accounts | 3 |

**Project status પ્રમાણે:** Synopsis Under Review 46 · Correction Required 21 · Rejected 10 · Synopsis Approved 41 · Final Submitted 22 · Final Reviewed 12 · Completed 30.

Final submissions માં નાની સાચી PDF/ZIP ફાઈલો મૂકેલી છે, એટલે download links કામ કરે છે.

### Login (password)

| Role | Email | Password |
|---|---|---|
| HOD | `hod.bca@bulk.spss.test`, `hod.msc@bulk.spss.test` | `Password123` |
| Exam Coordinator | `exam@bulk.spss.test` | `Password123` |
| Office Clerk | `office@bulk.spss.test` | `Password123` |
| Faculty (seeder) | `faculty1@bulk.spss.test` … `faculty8@bulk.spss.test` | `password` |
| Faculty (ફોર્મ થી) | `faculty9@bulk.spss.test` … `faculty16@bulk.spss.test` | `Password123` |
| Students | દા.ત. `mansi.soni.178@bulk.spss.test` (Admin → Students માં બધા email દેખાશે) | `password` |

### Backup / પાછું લાવવું

- ડેટા ઉમેરતા પહેલાંનું DB: `E:\wamp64\www\student-project-management-system-unused-backup\storage\backups\before-bulk-seed-2026-10-11.sql`
- જૂનો DB પાછો લાવવો હોય તો phpMyAdmin માં `student_submission` DB માં આ ફાઈલ import કરો.

## 2. ટેસ્ટ પરિણામ

| ટેસ્ટ | પરિણામ |
|---|---|
| PHPUnit (હાલના automated tests) | **186 / 186 pass** |
| 500 students સાથે બધા pages ખોલવા (super admin, admin, faculty, 4 પ્રકારના students, pending, guest) | બધા pages ખૂલ્યા, કોઈ 500 error નથી |
| Permission tests: બીજાની ફાઈલ, બીજા faculty નો project, student દ્વારા admin page | બધું યોગ્ય રીતે block થયું (403 / redirect) |
| સાચા ફોર્મ થી ઉમેરવું: roles, staff, faculty | pass |
| Guide assign કરવો, bulk assign, guide બદલવો (history સાથે) | pass |
| Synopsis approve / correction / reject, final review + marks, assignment check | pass |
| Validation: weak password, duplicate email, max થી વધુ marks | pass |
| Office Clerk દ્વારા pending accounts approve કરવા | pass |
| **કુલ workflow tests** | **55 pass, 1 fail** (નીચે bug #2) |

નોંધ: `admin` role ને Workload, Approvals, Alumni, Roles, Settings જેવા pages ખોલવાની permission આપેલી નથી, એટલે ત્યાં redirect થાય છે. આ bug નથી. જોઈએ તો Super Admin → Roles માં permission આપી શકાય.

## ✅ Fix status (11-10-2026, પછીથી ઉમેર્યું)

નીચેના **બધા 18 bugs fix થઈ ગયા છે**. Design/UI માં કોઈ ફેરફાર નથી અને git ને હાથ લગાડ્યો નથી.

- દરેક fix માટે નવો test: `tests/Feature/BugFixRegressionTest.php` (18 tests).
- હવે કુલ **204 / 204 tests pass** થાય છે.
- Live DB પર ફરી ચકાસ્યું: staff હવે Super Admin બનાવી શકતો નથી, અને duplicate role પર 500 ને બદલે validation message આવે છે.
- Assignment export / distribution pages: **~560 → ~10 DB queries**.
  - PDF બનાવવામાં હજી ~20 સેકન્ડ લાગે છે, કારણ કે DomPDF માં DejaVu font સાથે ~600 rows render થાય છે.
  - Font બદલવાથી PDF નો દેખાવ બદલાય, એટલે તે બદલ્યો નથી.
  - ઉપાય: export પહેલાં Department / Semester filter પસંદ કરો.
- નોંધ: notification emails હવે response તૈયાર થયા પછી (`defer`) મોકલાય છે. આ માટે queue worker ની જરૂર નથી.

## 3. મળેલા બગ્સ (સૌથી ગંભીર પહેલાં)

✅ = live ટેસ્ટમાં ખાતરી થઈ · 🔍 = code વાંચીને ખાતરી થઈ

### Critical / High

1. ✅ **સામાન્ય staff પોતાને Super Admin બનાવી શકે છે.** જે role પાસે `admins.manage` હોય તે નવો `super_admin` બનાવી શકે છે, અથવા અસલી super admin નો password બદલી શકે છે.
   - ક્યાં: `app/Http/Requests/Admin/StoreAdminRequest.php:21`, `UpdateAdminRequest.php`, `AdminUserController.php`
   - ઉપાય: super_admin role કે super_admin user ને માત્ર super admin જ બનાવી કે બદલી શકે.
2. ✅ **એક જ નામનો role બીજી વાર બનાવતાં 500 crash.** દા.ત. "HOD" બે વાર, અથવા "Admin" / "Faculty" નામ.
   - ક્યાં: `app/Http/Controllers/Admin/RoleController.php:32`
   - ઉપાય: slug unique છે કે નહીં તે validation માં તપાસવું.
3. 🔍 **Faculty delete કરતાં આખો academic record ઊડી જાય છે.** તેના assignments, students ના submissions, reviews અને guide history બધું cascade થી delete થાય છે.
   - ક્યાં: `Admin/FacultyController.php:103`
   - ઉપાય: data હોય તો delete ન કરવા દેવું, અથવા account block કરવું.
4. 🔍 **Department delete કરતાં તેના બધા assignments અને submissions delete થાય છે.** Students અને projects નો department ખાલી (NULL) થઈ જાય છે.
   - ક્યાં: `Admin/DepartmentController.php:58`
   - ઉપાય: students, projects કે assignments હોય તો delete ન કરવા દેવું.
5. 🔍 **Group partner synopsis edit કરે તો પોતે જ project માંથી નીકળી જાય છે.** Correction / Rejected વખતે partner "Single" પસંદ કરે તો તેનું membership delete થાય છે.
   - ક્યાં: `Student/SynopsisController.php:195-205`

### Medium

6. 🔍 Alumni ને Edit કરીને save કરતાં તે ફરી "Active" student બની જાય છે, કારણ કે status dropdown માં "graduated" option નથી (`Admin/StudentController.php:175`).
7. 🔍 Blocked કે pending student ને "Move to Alumni" કરતાં તે login કરી શકે છે (`AlumniController.php:108`).
8. 🔍 Admin એ બનાવેલા assignments માં `academic_year_id` save થતું નથી, એટલે તે ક્યારેય archive થતા નથી (`Admin/AssignmentController.php:146`).
9. 🔍 Assignment ના "X of Y submitted" ના કુલ (Y) માં અને નવા assignment ની notification માં alumni, blocked અને pending students પણ ગણાય છે (`Assignment::targetStudents()`).
10. 🔍 Distribution report માં "Submitted X / N" નો N આખા department ના students છે, તે semester ના નહીં.
11. 🔍 Final review "Mark Completed" વગર save કરો તો પણ student ને marks notification / email માં દેખાઈ જાય છે (`Faculty/ReviewController.php:90`).
12. 🔍 Notification emails એક પછી એક, request ની અંદર જ મોકલાય છે. 100+ students ને SMTP થી mail જાય તો timeout થઈ શકે (`Notification.php:47`). હાલ `MAIL_MAILER=log` છે એટલે દેખાતું નથી.
13. 🔍 Re-upload કરતાં જૂની ફાઈલો disk પરથી delete થતી નથી, એટલે 300 MB ના ZIP થી disk ભરાઈ શકે.
14. 🔍 Re-upload request ની `due_at` date ક્યારેય enforce થતી નથી.

### Low

15. Workload નું "Unchecked" column submissions નહીં પણ assignments ગણે છે.
16. Student import માં password rule `min:6` છે, બાકીની app માં 8 અક્ષર + letters + numbers.
17. Student edit ફોર્મમાં semester `1-8` hard-coded છે.
18. Completed / Final Reviewed project પર re-upload request આવે તો student ના page પર upload બટન દેખાતું નથી.

## 4. Performance (500+ students સાથે માપેલું)

| Page | સમય | DB queries | સ્થિતિ |
|---|---|---|---|
| `/admin/assignments/export-pdf` | **~22 સેકન્ડ** | 605 | ❌ ખૂબ ધીમું (N+1) |
| `/admin/assignments/distribution/pdf` | **~19 સેકન્ડ** | 559 | ❌ ખૂબ ધીમું |
| `/admin/assignments/export` | ~7 સેકન્ડ | 558 | ❌ N+1 |
| `/admin/assignments/distribution` | ~2 સેકન્ડ | 561 | ⚠️ N+1 |
| `/admin/students/export` | ~6 સેકન્ડ | 5 | ⚠️ Excel બનાવવામાં ધીમું |
| `/admin/assignments/distribution/excel` | ~6 સેકન્ડ | 10 | ⚠️ |
| `/faculty/assignments/{id}` | 0.3 સેકન્ડ | 32 | ⚠️ class મોટો થશે તો queries વધશે |
| બાકીના બધા list pages (students, projects, dashboard…) | 0.1–0.6 સેકન્ડ | 5–30 | ✅ બરાબર |

Students વધશે તેમ assignment ના exports અને distribution reports વધુ ધીમા થશે. Students/assignments ની relations eager-load કરવાથી (`with()` / `withCount()`) આ સુધરી શકે.
