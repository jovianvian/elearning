# Phase 7 Exam Engine - Implemented

## Delivered Scope
- Exam CRUD with schedule and state fields:
  - `start_at`, `end_at`, `duration_minutes`
  - `status`, `is_published`
  - `shuffle_questions`, `shuffle_options`
  - `auto_submit`, `max_attempts`
- Exam question attachment via `exam_questions`
- Student attempt flow:
  - start/resume attempt
  - one active unfinished attempt only
  - save answers
  - submit manually
  - auto submit on timeout/deadline
- Grading:
  - objective auto-scoring (multiple choice + short answer normalized match)
  - essay manual grading by teacher/admin/super admin
  - separate score fields: objective, essay, final
- Result publication:
  - manual publish by teacher/admin/super admin
  - updates `exam_attempts.is_published`
  - records `exam_publication_logs`
  - creates student notifications
- Exam monitoring events:
  - logs start/submit/auto-submit
  - student-side event endpoint for visibility, blur/focus, refresh, reconnect, duplicate session
  - updates suspicious counters and flags

## Access Rules Applied
- Super Admin/Admin:
  - full exam management and grading
- Teacher:
  - manage exams only for courses they teach
  - collaborative access within same course
- Principal:
  - read-only exam pages
- Student:
  - own exam list and own attempts/results only

## Notes
- `max_attempts` default is kept at `1` unless changed by teacher/admin.
- Result is hidden from student until manual publish (`is_published=true` on attempt).
- Short answer grading uses normalized exact match in MVP.

