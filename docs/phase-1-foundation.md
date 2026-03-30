# Phase 1 Foundation Lock - Teramia E-Learning

Date: 2026-03-30
Project: Teramia E-Learning
School: SMP Teramia
Stack: Laravel + MySQL + Blade + Tailwind CSS
Locales: id, en

## 1. Architecture Overview

### 1.1 Architectural Style
- Modular monolith using Laravel.
- Role-separated navigation and routes.
- Service-oriented application layer for exam, grading, import, logging, restore.
- Clean separation:
  - HTTP layer: Controllers, Middleware, Form Requests
  - Domain layer: Models + Policies
  - Application layer: Services
  - Infrastructure layer: Imports, logging, notification dispatching

### 1.2 Core Layers
- `app/Models`: entities + relationships + casts + soft delete where required.
- `app/Http/Controllers`: grouped by role scope and module.
- `app/Http/Requests`: validation and input normalization.
- `app/Services`: business logic (exam engine, imports, grading, restore, logging).
- `app/Policies`: ownership and permission enforcement.
- `routes/`: route groups per role and public auth flow.
- `resources/views`: role pages + shared components.
- `resources/lang/id`, `resources/lang/en`: bilingual UI strings.

### 1.3 Security and Access
- Roles are fixed to 5 values:
  - Super Admin
  - Admin
  - Principal
  - Teacher
  - Student
- Route middleware + policy checks + blade guards are mandatory.
- Principal remains read-only in all operational modules.

### 1.4 Critical Behavioral Locks
- Student login by NIS only; default password = NIS.
- `must_change_password = true` on initial student account.
- One active unfinished attempt per exam per student.
- Default `max_attempts = 1` unless teacher changes exam settings.
- Teacher collaboration only inside same course.
- Result visibility controlled manually by teacher (not auto-publish).
- Short answer grading in MVP: exact/normalized safe matching.

## 2. Role Permission Matrix (Lock)

### 2.1 Super Admin
- Full access to all modules.
- Can access restore center, audit logs, suspicious logs, settings, all reports.

### 2.2 Admin
- Operational management: users, classes, subjects, assignments, courses, exam operations, operational reports.
- No critical global settings or global restore center.

### 2.3 Principal
- Read-only dashboards and summaries.
- No create/update/delete/restore/grading/settings actions.

### 2.4 Teacher
- Own profile, own assigned courses, own exams/grading.
- Shared course collaboration: teacher can manage exams only in courses where assigned.
- Access subject-shared question banks according to rules.

### 2.5 Student
- Own profile, own courses, own exams, own results (if published), own notifications.
- No admin/teacher data access.

## 3. Route Map by Role (Final Plan)

### 3.1 Public
- `GET /login`
- `POST /login`
- `GET /password/forgot`
- `POST /password/forgot`
- `GET /password/reset/{token}`
- `POST /password/reset`
- `GET /force-change-password`
- `POST /force-change-password`

### 3.2 Super Admin
- `/super-admin/dashboard`
- `/super-admin/users/*`
- `/super-admin/roles-permissions/*`
- `/super-admin/academic-years/*`
- `/super-admin/semesters/*`
- `/super-admin/classes/*`
- `/super-admin/subjects/*`
- `/super-admin/assignments/*`
- `/super-admin/courses/*`
- `/super-admin/question-banks/*`
- `/super-admin/exams/*`
- `/super-admin/reports/*`
- `/super-admin/login-logs`
- `/super-admin/suspicious-activities`
- `/super-admin/restore-center/*`
- `/super-admin/settings/*`
- `/super-admin/profile/*`

### 3.3 Admin
- `/admin/dashboard`
- `/admin/users/*`
- `/admin/classes/*`
- `/admin/subjects/*`
- `/admin/assignments/*`
- `/admin/courses/*`
- `/admin/exams/*`
- `/admin/reports/*`
- `/admin/profile/*`

### 3.4 Principal
- `/principal/dashboard`
- `/principal/students-overview`
- `/principal/teachers-overview`
- `/principal/courses-overview`
- `/principal/exam-reports`
- `/principal/class-performance`
- `/principal/subject-performance`
- `/principal/activity-summary`
- `/principal/profile/*`

### 3.5 Teacher
- `/teacher/dashboard`
- `/teacher/courses/*`
- `/teacher/question-banks/*`
- `/teacher/question-imports/*`
- `/teacher/exams/*`
- `/teacher/grading/*`
- `/teacher/reports/*`
- `/teacher/notifications/*`
- `/teacher/profile/*`

### 3.6 Student
- `/student/dashboard`
- `/student/courses/*`
- `/student/exams/*`
- `/student/results/*`
- `/student/notifications/*`
- `/student/profile/*`

## 4. Final Table List (MVP Lock)

### 4.1 Access & Identity
- `roles`
- `users`
- `user_profiles`
- `password_reset_tokens`
- `login_logs`

### 4.2 Settings & Academic Periods
- `app_settings`
- `academic_years`
- `semesters`

### 4.3 School Structure
- `school_classes`
- `subjects`
- `class_students`
- `subject_teachers`

### 4.4 Course
- `courses`
- `course_teachers`
- `course_students`

### 4.5 Question Bank
- `question_banks`
- `questions`
- `question_options`
- `question_import_logs`

### 4.6 Exams
- `exams`
- `exam_questions`
- `exam_attempts`
- `exam_attempt_answers`
- `exam_publication_logs`

### 4.7 Monitoring & Logs
- `exam_session_logs`
- `tab_switch_logs`
- `suspicious_activity_logs`
- `activity_logs`
- `restore_logs`

### 4.8 Notifications
- `notifications`
- `user_notifications`

## 5. Relationship Lock (Core)

### 5.1 Identity
- `roles 1..n users`
- `users 1..1 user_profiles`
- `users 1..n login_logs`

### 5.2 Academic Structure
- `academic_years 1..n semesters`
- `academic_years 1..n school_classes`
- `school_classes n..n users(student)` via `class_students`
- `subjects n..n users(teacher)` via `subject_teachers`

### 5.3 Courses
- `courses n..1 subjects`
- `courses n..1 school_classes`
- `courses n..1 academic_years`
- `courses n..1 semesters`
- `courses n..n users(teacher)` via `course_teachers`
- `courses n..n users(student)` via `course_students`

### 5.4 Question Bank
- `question_banks n..1 subjects`
- `question_banks 1..n questions`
- `questions 1..n question_options`

### 5.5 Exams
- `exams n..1 courses`
- `exams 1..n exam_questions`
- `exams 1..n exam_attempts`
- `exam_attempts 1..n exam_attempt_answers`

### 5.6 Monitoring
- `exam_attempts 1..n exam_session_logs`
- `exam_attempts 1..n tab_switch_logs`
- `exam_attempts 1..n suspicious_activity_logs`

## 6. Entity Rules Lock

### 6.1 Users
- `must_change_password` required.
- Student: `nis` required, email nullable.
- Teacher: `nip` or username required, email required.
- Admin/Super Admin/Principal: username or email login, email required.

### 6.2 Course
- One course = one subject + one class + one academic year + one semester.
- Course may have multiple teachers.

### 6.3 Exam Attempt
- One active unfinished attempt per student per exam.
- Re-enter unfinished attempt is allowed if exam still active.
- No new attempt while unfinished attempt exists.

### 6.4 Grading
- Objective auto grading.
- Short answer: exact/normalized match (safe MVP).
- Essay manual grading + feedback.
- Store `score_objective`, `score_essay`, `final_score`.

### 6.5 Publishing
- Result visibility controlled manually by teacher.

### 6.6 Soft Delete
Must use soft delete for:
- users
- school_classes
- subjects
- courses
- question_banks
- questions
- exams

## 7. Phase 2 Readiness Checklist
- Architecture lock finalized.
- Permissions lock finalized.
- Route map lock finalized.
- Table list lock finalized.
- Relationship lock finalized.

Phase 2 next:
1. Build migrations (full locked schema)
2. Build models and relationships
3. Build seeders (roles, settings baseline, academic baseline)
