# Phase 9 Monitoring, Audit, Soft Delete, Restore - Implemented

## Scope Delivered
- Suspicious activity monitoring page:
  - Super Admin: full logs
  - Teacher: only logs for exams in owned courses
- Audit log page (Super Admin)
- Login log page (Super Admin)
- Restore center (Super Admin) with entity filter and restore action:
  - users
  - school_classes
  - subjects
  - courses
  - question_banks
  - questions
  - exams
- Restore action writes `restore_logs` and `activity_logs`.

## Activity Logging Strategy
- Added model observer-based activity logging for critical entities:
  - users
  - school_classes
  - subjects
  - courses
  - question_banks
  - questions
  - exams
  - app_settings
- Events tracked:
  - created
  - updated
  - deleted
  - restored

## Notes
- Soft delete is used on required entities and integrated with restore center.
- Monitoring logs remain separate from audit logs, aligned with lock document.

