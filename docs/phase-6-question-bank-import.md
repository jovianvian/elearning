# Phase 6 Question Bank and Import - Implemented

## Scope Delivered
- Question bank CRUD (`question_banks`) with visibility:
  - `subject_shared`
  - `private`
- Question CRUD (`questions`, `question_options`) for:
  - multiple choice
  - short answer
  - essay
- Import module:
  - AIKEN import (`.txt`) for multiple choice
  - CSV import (`.csv`) for multiple choice, short answer, essay
- Import logs (`question_import_logs`) with:
  - total rows
  - success count
  - failed count
  - row-level error list

## Access Rules Implemented
- Super Admin/Admin:
  - full access question bank + import module
- Teacher:
  - can access banks in own assigned subject (`subject_teachers.is_active = true`)
  - can view:
    - subject shared banks
    - own private banks
  - can manage:
    - own banks
    - shared banks in assigned subject
- Student/Principal:
  - no access to question bank/import management routes

## Import Validation Rules
- AIKEN:
  - requires question text
  - minimum 2 options (`A-E`)
  - requires `ANSWER: X`
  - answer key must exist in provided options
- CSV:
  - required headers: `type, question_text, points, difficulty`
  - type must be `multiple_choice|short_answer|essay`
  - points > 0
  - difficulty in `easy|medium|hard`
  - multiple choice:
    - min 2 options
    - `correct_option` must match option keys
  - short answer:
    - `short_answer_key` required

## Notes
- Failed import rows are not silently ignored and are recorded in `question_import_logs.error_log`.
- Manual question entry uses `import_source = manual`.
- Imported question entry uses `import_source = aiken|csv`.

