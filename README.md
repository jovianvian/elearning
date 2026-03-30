# EduSasana LMS - Fondasi MVP E-Learning Sekolah

Project ini adalah implementasi awal aplikasi e-learning sekolah berbasis Laravel + MySQL dengan pendekatan Agile.

## 1) Rekomendasi Nama Aplikasi

Pilihan nama:
1. EduSasana
2. KelasNusa
3. SkoolaHub
4. Belajarin
5. CiptaKelas
6. RuangDidik
7. ScolaOne
8. KelasPintar+

Nama terbaik: **EduSasana**
- Modern dan profesional
- Relevan dengan konteks pendidikan formal
- Mudah di-branding untuk sekolah
- Cukup unik untuk identitas produk jangka panjang

## 2) Deskripsi Singkat Aplikasi

**EduSasana** membantu sekolah mengelola pembelajaran digital terpusat untuk admin, guru, dan siswa.

Masalah yang diselesaikan:
- Data pembelajaran tersebar (chat, dokumen, dan catatan manual)
- Sulit memantau kelas, materi, dan akses pengguna
- Tidak ada alur digital standar untuk aktivitas belajar dasar

Manfaat:
- Satu portal login untuk semua peran
- Data terstruktur (kelas, mapel, materi)
- Fondasi scalable untuk fitur lanjutan (tugas, penilaian, ujian online)

## 3) Fitur Inti MVP

Prioritas MVP tahap pertama:
1. Login berbasis database (email + password)
2. Role user dasar (`admin`, `guru`, `siswa`)
3. Dashboard sederhana per role
4. Struktur data awal: users, roles, classes, subjects, materials
5. Navigasi umum aplikasi (sidebar + topbar + logout)

Roadmap tahap berikutnya:
- CRUD kelas/mapel/materi
- Penugasan siswa
- Tracking progres belajar

## 4) Agile Planning Awal

### Product Vision
Menyediakan platform e-learning sekolah yang stabil, sederhana, dan siap dikembangkan bertahap tanpa over-engineering di fase awal.

### User Roles
- Admin: kelola pengguna, kelas, struktur akademik
- Guru: kelola materi dan aktivitas kelas
- Siswa: akses materi dan dashboard pembelajaran

### User Stories Utama
- Sebagai admin, saya ingin login dan melihat ringkasan data sekolah.
- Sebagai guru, saya ingin login dan melihat jumlah mapel/materi saya.
- Sebagai siswa, saya ingin login dan melihat materi yang tersedia.

### Product Backlog Awal
1. Setup project Laravel + MySQL
2. Authentication login/logout
3. Role-based authorization
4. Dashboard per role
5. Migration tabel inti
6. Seeder role + akun awal
7. UI layout umum

### Sprint Planning (3 Sprint)

**Sprint 1 (Fondasi Teknis)**
- Setup Laravel
- Konfigurasi MySQL
- Migration users/roles dan relasi role
- Seeder role + akun default

**Sprint 2 (Akses & Navigasi)**
- Auth controller (login/logout)
- Middleware role
- Routing per role
- Layout aplikasi (login + shell dashboard)

**Sprint 3 (Akademik Dasar)**
- Migration classes, subjects, materials
- Relasi model
- Dashboard statistik awal
- Hardening dan smoke test

## 5) Diagram Perancangan (Teks)

### Use Case Diagram (Teks)
**Actor: Admin**
- Login
- Akses dashboard admin
- Monitoring data user/kelas/mapel/materi

**Actor: Guru**
- Login
- Akses dashboard guru
- Monitoring mapel dan materi yang dibuat

**Actor: Siswa**
- Login
- Akses dashboard siswa
- Melihat ringkasan materi tersedia

### Activity Diagram Login (Teks)
1. User buka halaman login
2. User isi email + password
3. Sistem validasi input
4. Sistem cek kredensial ke database
5. Jika gagal: kembali ke login + pesan error
6. Jika sukses: regenerate session
7. Sistem redirect ke dashboard sesuai role

### ERD Awal (Teks)
- `roles (1) --- (N) users`
- `users (1) --- (N) subjects` melalui `teacher_id`
- `users (1) --- (N) materials` melalui `created_by`
- `users (1) --- (N) school_classes` melalui `homeroom_teacher_id` (nullable)
- `school_classes (1) --- (N) subjects`
- `subjects (1) --- (N) materials`

### Flow Proses Umum Aplikasi
1. User login
2. Middleware `auth` cek sesi
3. Middleware `role` cek hak akses per endpoint
4. User masuk dashboard role masing-masing
5. User navigasi modul dasar (kelas, mapel, materi)

## 6) Desain Database Awal MySQL

### Tabel `roles`
- `id` BIGINT PK
- `name` VARCHAR(50) UNIQUE (`admin|guru|siswa`)
- `display_name` VARCHAR(100)
- `timestamps`

### Tabel `users`
- `id` BIGINT PK
- `name` VARCHAR(255)
- `email` VARCHAR(255) UNIQUE
- `role_id` BIGINT FK -> `roles.id` (nullable on delete)
- `email_verified_at` TIMESTAMP nullable
- `password` VARCHAR(255)
- `remember_token`
- `timestamps`

### Tabel `school_classes`
- `id` BIGINT PK
- `name` VARCHAR(100)
- `code` VARCHAR(30) UNIQUE
- `grade_level` TINYINT nullable
- `homeroom_teacher_id` BIGINT FK -> `users.id` nullable
- `timestamps`

### Tabel `subjects`
- `id` BIGINT PK
- `school_class_id` BIGINT FK -> `school_classes.id`
- `teacher_id` BIGINT FK -> `users.id` nullable
- `name` VARCHAR(100)
- `code` VARCHAR(30) nullable
- `description` TEXT nullable
- `timestamps`

### Tabel `materials`
- `id` BIGINT PK
- `subject_id` BIGINT FK -> `subjects.id`
- `created_by` BIGINT FK -> `users.id`
- `title` VARCHAR(150)
- `content` TEXT nullable
- `attachment_path` VARCHAR(255) nullable
- `published_at` TIMESTAMP nullable
- `timestamps`

## 7) Arsitektur Laravel Awal

### Model
- `User`
- `Role`
- `SchoolClass`
- `Subject`
- `Material`

### Controller
- `AuthController` (show login, login attempt, logout)
- `DashboardController` (redirect role + dashboard admin/guru/siswa)

### Middleware
- `auth` (built-in)
- `role` (custom `RoleMiddleware`)

### Route Utama
- `GET /login`
- `POST /login`
- `POST /logout`
- `GET /dashboard`
- `GET /admin/dashboard`
- `GET /guru/dashboard`
- `GET /siswa/dashboard`

### Blade/Layout
- `resources/views/auth/login.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/dashboard/admin.blade.php`
- `resources/views/dashboard/guru.blade.php`
- `resources/views/dashboard/siswa.blade.php`

## 8) Implementasi Awal yang Sudah Dibuat

Sudah diimplementasikan:
- Login berbasis DB (`Auth::attempt`)
- Role user dasar admin/guru/siswa
- Redirect dashboard berdasarkan role
- Dashboard sederhana per role
- UI umum aplikasi (login, sidebar, topbar, logout)
- Seeder akun awal

Belum (roadmap):
- Upload tugas
- Penilaian detail
- Ujian online
- Notifikasi lanjutan

## 9) Gambaran UI/UX (Wireframe Teks)

### Login
- Card di tengah layar
- Input email/password + checkbox remember
- Error state jelas
- Catatan akun demo

### Dashboard Admin
- Sidebar menu kiri
- Topbar user info + badge role
- Card statistik user/kelas/mapel/materi

### Dashboard Guru
- Struktur sama dengan admin agar konsisten
- Statistik mapel diampu, materi dibuat, kelas terlibat

### Dashboard Siswa
- Struktur sama
- Statistik materi tersedia, mapel aktif, kelas aktif

### Nuansa Visual
- Dominan biru sekolah (`#0f4c81`)
- Background terang dan bersih
- Komponen rounded, kontras rapi, mobile-friendly

## 10) Output Teknis Awal

### Struktur Module Penting
```text
app/
  Http/
    Controllers/
      AuthController.php
      DashboardController.php
    Middleware/
      RoleMiddleware.php
  Models/
    User.php
    Role.php
    SchoolClass.php
    Subject.php
    Material.php
database/
  migrations/
  seeders/
resources/views/
  auth/login.blade.php
  layouts/app.blade.php
  dashboard/{admin,guru,siswa}.blade.php
routes/web.php
```

### Migration yang Dibuat Lebih Dulu
1. `create_roles_table`
2. `add_role_id_to_users_table`
3. `create_school_classes_table`
4. `create_subjects_table`
5. `create_materials_table`

### Route Awal
- `login`, `login.attempt`, `logout`
- `dashboard`, `dashboard.admin`, `dashboard.guru`, `dashboard.siswa`

### Langkah Implementasi Bertahap
1. `composer install`
2. `cp .env.example .env` lalu set MySQL
3. `php artisan key:generate`
4. `php artisan migrate:fresh --seed`
5. `php artisan serve`

## 11) Kode Awal Inti yang Sudah Siap

Sudah tersedia dalam project ini:
- Konfigurasi `.env.example` untuk MySQL
- Migration `roles` + relasi ke `users`
- Auth login/logout sederhana
- `RoleMiddleware` untuk pembatasan akses
- Dashboard basic per role
- Layout Blade dasar sebagai fondasi jangka panjang

## Akun Demo

Password semua akun demo: `password123`
- `admin@edusasana.sch.id`
- `guru@edusasana.sch.id`
- `siswa@edusasana.sch.id`
