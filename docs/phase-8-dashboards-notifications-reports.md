# Phase 8 Dashboards, Notifications, Reports - Implemented

## Scope Delivered
- Role dashboards upgraded (Super Admin, Admin, Principal, Teacher, Student).
- Notification module:
  - user notification list
  - mark single notification as read
  - mark all as read
- Report module (MVP):
  - exam recap by status
  - exam result per class
  - exam result per subject
  - student score list per exam
  - login activity summary
  - suspicious activity summary

## Role Alignment
- Super Admin: system-wide overview including deleted items, audit and login/session summary, settings summary.
- Admin: operational summary (users, active/inactive, blocked, classes, courses, active exams, daily activity).
- Principal: read-only class/subject performance, score summary, exam recap context.
- Teacher: own operational dashboard (courses, grading queue, question banks, exam stats).
- Student: active courses, pending exams, published results, profile summary, latest notifications.

## Notes
- Report access restricted to `super_admin`, `admin`, `principal`, `teacher`.
- Notification access enabled for all authenticated roles.
- Dashboard UI aligned to Tailwind-based app layout.

