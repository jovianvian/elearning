# Teramia E-Learning (MVP Foundation)

Teramia E-Learning adalah aplikasi e-learning dan ujian online berbasis Laravel untuk **SMP Teramia**.

Stack utama:
- Laravel
- MySQL
- Blade + Tailwind CSS
- Bilingual locale (`id`, `en`)

## MVP Scope
Sudah mencakup:
- Multi-role auth: `Super Admin`, `Admin`, `Principal`, `Teacher`, `Student`
- Login rules:
  - Student login via `NIS`, default password = NIS, first-login force change password
  - Teacher login via `NIP` atau `username`
  - Admin/Super Admin/Principal login via `username` atau `email`
- Forgot/reset password via email
- App settings & branding
- Academic year, semester, class, subject, user management
- Student-class assignment, teacher-subject assignment
- Course management (multi teacher, auto expose student by class via snapshot)
- Question bank & question CRUD
- Import question:
  - AIKEN (MCQ)
  - CSV (MCQ, short answer, essay)
- Exam engine:
  - schedule, timer, shuffle, auto submit
  - objective auto-grade + essay manual grade
  - manual result publish by teacher/admin/super admin
- Notifications dashboard
- Reports MVP
- Suspicious activity logging
- Audit logs
- Soft delete + restore center
- Localization base (ID/EN)

## Requirements
- PHP 8.2+
- Composer 2+
- MySQL 8+ / MariaDB compatible

## Installation
```bash
git clone https://github.com/jovianvian/elearning.git
cd elearning
composer install
cp .env.example .env
php artisan key:generate
```

## Environment Setup (`.env`)
```env
APP_NAME="Teramia E-Learning"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=teramia_elearning
DB_USERNAME=root
DB_PASSWORD=
```

## Database Setup
```bash
php artisan migrate:fresh --seed
```

## Run Application
```bash
php artisan serve
```
App URL: `http://127.0.0.1:8000`

## Default Accounts (Seeder)
- Super Admin
  - username: `superadmin`
  - email: `superadmin@teramia.sch.id`
  - password: `Password123!`
- Admin
  - username: `admin`
  - email: `admin@teramia.sch.id`
  - password: `Password123!`
- Principal
  - username: `principal`
  - email: `principal@teramia.sch.id`
  - password: `Password123!`
- Teacher examples
  - `teacher.mtk` / `teacher.mtk@teramia.sch.id` / password `Password123!`
  - `teacher.ipa` / `teacher.ipa@teramia.sch.id` / password `Password123!`
  - `teacher.big` / `teacher.big@teramia.sch.id` / password `Password123!`
- Student examples
  - NIS: `7001` (password default: `7001`, first login must change password)
  - NIS: `7002` (password default: `7002`, first login must change password)
  - NIS: `7003` (password default: `7003`, first login must change password)

## Main Route Modules
- `/dashboard`
- `/users`, `/classes`, `/subjects`, `/courses`
- `/question-banks`, `/question-imports`
- `/exams`, `/exam-grading`, `/student-exams`
- `/reports`
- `/notifications`
- `/super-admin/audit-logs`
- `/super-admin/login-logs`
- `/super-admin/restore-center`
- `/suspicious-activities`

## Core Tables
- Access/identity: `roles`, `users`, `user_profiles`, `login_logs`
- Settings/academic: `app_settings`, `academic_years`, `semesters`
- Structure: `school_classes`, `subjects`, `class_students`, `subject_teachers`
- Course: `courses`, `course_teachers`, `course_students`
- Question bank: `question_banks`, `questions`, `question_options`, `question_import_logs`
- Exams: `exams`, `exam_questions`, `exam_attempts`, `exam_attempt_answers`, `exam_publication_logs`
- Monitoring/logs: `exam_session_logs`, `tab_switch_logs`, `suspicious_activity_logs`, `activity_logs`, `restore_logs`
- Notifications: `notifications`, `user_notifications`

## Verification Checklist
Lihat dokumen:
- `docs/phase-1-foundation.md` s/d `docs/phase-9-monitoring-audit-restore.md`
- `docs/final-verification-checklist.md`

