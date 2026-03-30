# Phase 2 Data Foundation - Implemented

## Completed Scope
- Full locked migration baseline for Teramia MVP tables.
- Core model foundation with relationships and soft delete on locked entities.
- Baseline seeders for:
  - 5 fixed roles
  - academic year + semesters
  - SMP class structure 7A..9C
  - subject list bilingual
  - default users (super admin, admin, principal, teachers, students)
  - app settings baseline + active period

## Verification
- `php artisan migrate:fresh --seed` => success
- table count check in MySQL => success
- `php artisan test` => pass

## Notes
- Legacy `materials` table from old MVP was removed by migration `2026_03_31_000008_drop_legacy_materials_table.php` to align with lock schema.
- Current repository still contains old controllers/views from pre-lock MVP and will be replaced progressively in Phase 3+.
