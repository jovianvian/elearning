# Final Verification Checklist (MVP)

## Authentication
- [ ] Student login with NIS works
- [ ] Student default password = NIS works
- [ ] Student first login forces password change
- [ ] Teacher login with NIP/username works
- [ ] Admin/Super Admin/Principal login with username/email works
- [ ] Forgot/reset password works for users with email

## Authorization
- [ ] Role-based route protection works
- [ ] Principal cannot create/update/delete operational data
- [ ] Teacher can only manage exams in assigned courses
- [ ] Student only accesses own courses/exams/results

## Academic Structure
- [ ] Academic year CRUD works
- [ ] Semester CRUD works
- [ ] Class CRUD works
- [ ] Subject CRUD works
- [ ] Student-class assignment works
- [ ] Teacher-subject assignment works

## Course
- [ ] Course CRUD works
- [ ] Multiple teacher assignment per course works
- [ ] Student course snapshot sync from class roster works

## Question Bank
- [ ] Question bank CRUD works
- [ ] Manual question creation works for MCQ/short answer/essay
- [ ] AIKEN import works and logs success/failed rows
- [ ] CSV import works and logs success/failed rows

## Exam Engine
- [ ] Exam CRUD + scheduling works
- [ ] Timer displays and counts down in attempt page
- [ ] Save draft answer works
- [ ] Manual submit works
- [ ] Auto submit on timeout works
- [ ] Objective score auto-calculates
- [ ] Essay grading form works
- [ ] Result publish is manual (not automatic)
- [ ] Student sees result only after publish
- [ ] Max attempts enforced (default 1)
- [ ] Single active unfinished attempt enforced

## Monitoring & Logs
- [ ] Exam event logs (start/submit/auto submit/refresh/reconnect/blur/focus/visibility) recorded
- [ ] Suspicious logs page works for Super Admin and Teacher scope
- [ ] Audit log page works (Super Admin)
- [ ] Login log page works (Super Admin)

## Restore Center
- [ ] Soft delete on required entities works
- [ ] Restore center lists deleted data by entity
- [ ] Restore action works
- [ ] Restore logs recorded

## Dashboard, Notifications, Reports
- [ ] Role dashboards render with expected stats
- [ ] Notification list + mark read works
- [ ] Reports page loads (exam recap, class/subject results, login summary, suspicious summary)
- [ ] Exam score per exam report page loads

