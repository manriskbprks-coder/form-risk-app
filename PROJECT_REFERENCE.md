# 📘 PROJECT REFERENCE — BPR Risk Management System

> **Tech Stack:** Laravel 11 · Spatie Permission · Alpine.js · Tailwind CSS · MySQL (prod) / SQLite (test)  
> **Role-Based Access Control:** 4 Role Categories (maker, checker, viewer, admin)  
> **Total Tests:** 272 passing · 2 skipped · 8 Testing Phases

---

## 📋 DAFTAR ISI

1. [Architecture](#1-architecture)
2. [File Structure](#2-file-structure)
3. [Database Schema](#3-database-schema)
4. [User Functions (Model)](#4-user-functions-model)
5. [Route Functions (Detail)](#5-route-functions-detail)
6. [API Endpoints](#6-api-endpoints)
7. [Roles & Role Categories](#7-roles--role-categories)
8. [Flow Bisnis](#8-flow-bisnis)
9. [Security Features](#9-security-features)
10. [Testing](#10-testing)
11. [Environment & Config](#11-environment--config)

---

## 1. ARCHITECTURE

### 🏗️ High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                        BROWSER (User)                               │
│  Blade Templates + Alpine.js + Tailwind CSS                         │
└──────────────────────────┬──────────────────────────────────────────┘
                           │ HTTP Request
                           ▼
┌──────────────────────────────────────────────────────────────────────┐
│                    LARAVEL ROUTING (web.php)                         │
│                                                                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐                │
│  │ Auth Routes  │  │ User Routes  │  │ Admin Routes │                │
│  │ (guest)      │  │ (auth)       │  │ (auth+role)  │                │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘                │
│         │                 │                 │                        │
└─────────┼─────────────────┼─────────────────┼────────────────────────┘
          │                 │                 │
          ▼                 ▼                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     MIDDLEWARE STACK                                │
│                                                                     │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│  │ Security │ │  Force   │ │  Check   │ │ Throttle │ │  Role/   │   │
│  │ Headers  │ │  HTTPS   │ │ Password │ │ (5,10/m) │ │  Auth    │   │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘   │
└─────────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     CONTROLLER LAYER                                │
│                                                                     │
│  ┌─────────────────────┐  ┌───────────────────────┐                 │
│  │ RiskReportController│  │ RiskFreeDeclaration   │                 │
│  │ - create/store      │  │ Controller            │                 │
│  │ - review            │  │ - create/store        │                 │
│  │ - updateStatus      │  │ - violate             │                 │
│  │ - updateResolution  │  │ - history             │                 │
│  │ - addProgress       │  └───────────────────────┘                 │
│  │ - show/index        │                                            │
│  │ - requestRevision   │  ┌───────────────────────┐                 │
│  │ - submitRevision    │  │ Admin Controllers     │                 │
│  │ - approveRevision   │  │ - UserController      │                 │
│  └─────────────────────┘  │ - RoleController      │                 │
│  ┌─────────────────────┐  │ - RiskMasterController│                 │
│  │ ExportRiskReport    │  │ - BranchManagement    │                 │
│  │ Controller          │  └───────────────────────┘                 │
│  └─────────────────────┘                                            │
└─────────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     AUTHORIZATION LAYER                             │
│                                                                     │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │              RiskReportPolicy (Gates)                        │   │
│  │                                                              │   │
│  │  view() → admin=all, viewer=supervised, checker=own_branch,  │   │
│  │           maker=own_report                                   │   │
│  │  approve() → checker only, own branch, pending/need_revision │   │
│  │  updateProgress() → admin/viewer=blocked, others=view()      │   │
│  │  close() → checker only, own branch                          │   │
│  │  requestRevision() → manrisk only, approved only             │   │
│  │  submitRevision() → maker/checker, need_revision only        │   │
│  │  approveRevision() → manrisk only, pending_revision only     │   │
│  └──────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────────┐
│                       MODEL LAYER (Eloquent ORM)                    │
│                                                                     │
│  User ──┬── Branch (belongsTo)                                      │
│          ├── supervisedBranches (hasMany)                           │
│          └── Roles (Spatie) ── role_category (ENUM)                 │
│                                                                     │
│  RiskReport ──┬── User (belongsTo)                                  │
│                ├── Branch (belongsTo)                               │
│                ├── RiskItem (belongsTo)                             │
│                ├── RiskCause (belongsTo)                            │
│                └── RiskReportLog (hasMany)                          │
│                                                                     │
│  RiskItem ─── RiskCause ─── RiskMitigation                          │
│                                                                     │
│  RiskFreeDeclaration ──┬── Branch                                   │
│                         ├── User                                    │
│                         └── RiskFreeDeclarationDetail               │
│                                                                     │
│  Notification ──┬── User                                            │
│                  └── RiskReport                                     │
└─────────────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     DATABASE LAYER                                  │
│                                                                     │
│  ┌──────────────┐     ┌─────────────────┐     ┌──────────────┐      │
│  │  Production  │     │   Testing       │     │   Session    │      │
│  │  (MySQL)     │     │ (SQLite :memory)│     │  (file/db)   │      │
│  └──────────────┘     └─────────────────┘     └──────────────┘      │
└─────────────────────────────────────────────────────────────────────┘
```

### 🔄 Alur Data Role Category

```
┌──────────┐      ┌──────────┐     ┌────────────────┐
│  User    │────▶│  Role    │────▶│ role_category  │
│  (model) │      │ (Spatie) │     │ (ENUM di roles)│
└──────────┘      └──────────┘     └──────┬─────────┘
                                          │
                    ┌─────────────────────┼─────────────────────┐
                    │                     │                     │
                    ▼                     ▼                     ▼
              ┌──────────┐         ┌──────────┐          ┌──────────┐
              │  maker   │         │ checker  │          │ viewer   │
              │          │         │          │          │          │
              │ teller   │         │ kacab    │          │ korwil   │
              │ ca       │         │          │          │          │
              │ csr      │         │          │          │ admin    │
              │ security │         │          │          │          │
              └──────────┘         └──────────┘          │ manrisk  │
                                                         └──────────┘
```

### 🧠 MVC Pattern

```
┌─────────────────────────────────────────────────────────────────────┐
│  VIEW (Blade)          CONTROLLER              MODEL (DB)           │
│                                                                     │
│  User clicks           RiskReportController    RiskReport::create() │
│  "Submit Report"  ──▶  @store()           ──▶  (INSERT INTO        │
│                        validates request         risk_reports)      │
│                        checks policy                                │
│                        creates notification                         │
│                        logs activity                                │
│                        returns redirect                             │
│                                                                     │
│  User sees        ◀──  redirect()->back()  ◀──  success message    │
│  "Success!"                                                         │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 2. FILE STRUCTURE

```
form_risk/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── RiskMasterController.php    # CRUD master data risiko
│   │   │   │   └── RoleController.php           # CRUD role + role_category
│   │   │   ├── AdminUserController.php          # Manajemen user (ManRisk)
│   │   │   ├── Auth/
│   │   │   │   └── AuthenticatedSessionController.php  # Login + throttle
│   │   │   ├── ExportRiskReportController.php   # Export CSV
│   │   │   ├── NotificationController.php       # In-app notifications
│   │   │   ├── ProfileController.php            # Edit profile/password
│   │   │   ├── RiskFreeDeclarationController.php # Deklarasi nihil risiko
│   │   │   └── RiskReportController.php         # CRUD utama laporan risiko
│   │   ├── Middleware/
│   │   │   ├── CheckPasswordExpiry.php          # Paksa ganti password 90hr
│   │   │   ├── ForceHttps.php                   # Paksa HTTPS di prod
│   │   │   └── SecurityHeaders.php              # Security headers global
│   │   └── Requests/
│   │       ├── Auth/LoginRequest.php            # Validasi login
│   │       ├── StoreRiskReportRequest.php       # Validasi + sanitasi laporan
│   │       ├── UpdateRiskApprovalStatusRequest.php  # Validasi approve/reject
│   │       ├── UpdateRiskResolutionRequest.php   # Validasi update status
│   │       └── AddRiskProgressRequest.php        # Validasi catatan progress
│   │
│   ├── Models/
│   │   ├── Branch.php                           # Cabang BPR
│   │   ├── Notification.php                     # Notifikasi in-app
│   │   ├── RiskCause.php                        # Penyebab risiko
│   │   ├── RiskFreeDeclaration.php              # Deklarasi nihil header
│   │   ├── RiskFreeDeclarationDetail.php        # Detail per jabatan
│   │   ├── RiskItem.php                         # Item/master risiko
│   │   ├── RiskMitigation.php                   # Mitigasi risiko
│   │   ├── RiskReport.php                       # Laporan risiko utama
│   │   ├── RiskReportLog.php                    # Log aktivitas laporan
│   │   └── User.php                             # User + role helpers
│   │
│   ├── Policies/
│   │   └── RiskReportPolicy.php                 # Otorisasi berbasis role_category
│   │
│   └── Providers/
│       └── AppServiceProvider.php               # Global bindings
│
├── bootstrap/
│   └── app.php                                  # Middleware registration
│
├── config/
│   ├── app.php                                  # App config
│   ├── cors.php                                 # CORS settings
│   └── database.php                             # DB connections
│
├── database/
│   ├── factories/
│   │   ├── BranchFactory.php
│   │   ├── RiskCauseFactory.php
│   │   ├── RiskItemFactory.php
│   │   ├── RiskMitigationFactory.php
│   │   ├── RiskReportFactory.php
│   │   └── UserFactory.php
│   ├── migrations/                              # 28 migration files
│   ├── seeders/
│   │   ├── BranchSeeder.php
│   │   ├── DatabaseSeeder.php
│   │   ├── DummyRiskReportSeeder.php
│   │   ├── RiskMasterSeeder.php
│   │   └── UserSeeder.php
│   └── mongo/                                   # (opsional)
│
├── resources/
│   ├── css/app.css                              # Tailwind + custom styles
│   ├── views/
│   │   ├── admin/
│   │   │   ├── risk_master/index.blade.php      # Master data risiko
│   │   │   ├── roles/index.blade.php            # Manajemen role
│   │   │   └── users/index.blade.php            # Manajemen user
│   │   ├── auth/login.blade.php                 # Halaman login
│   │   ├── errors/                              # 403, 404, 419, 500
│   │   ├── layouts/app.blade.php                # Layout utama + sidebar
│   │   ├── notifications/index.blade.php        # Notifikasi in-app
│   │   ├── profile/                             # Edit profile
│   │   ├── risk_free_declarations/
│   │   │   ├── create.blade.php                 # Form deklarasi nihil
│   │   │   └── history.blade.php                # Riwayat deklarasi
│   │   ├── risk_reports/
│   │   │   ├── create.blade.php                 # Form laporan risiko
│   │   │   ├── index.blade.php                  # Riwayat/monitoring
│   │   │   ├── review.blade.php                 # Review & tindak lanjut
│   │   │   └── show.blade.php                   # Detail laporan
│   │   └── dashboard.blade.php                  # Dashboard + chart
│
├── routes/
│   ├── auth.php                                 # Auth routes (login, register, etc)
│   └── web.php                                  # Semua route aplikasi
│
├── tests/
│   ├── Browser/
│   │   └── Phase8E2ETest.php                    # E2E browser test (Dusk)
│   └── Feature/
│       ├── DashboardTest.php                    # Dashboard per role
│       ├── MasterRiskControllerTest.php         # CRUD master risiko
│       ├── Phase1ConfigTest.php                 # Security config audit
│       ├── Phase2AuthTest.php                   # Auth security tests
│       ├── Phase3AuthorizationTest.php          # Authorization per role
│       ├── Phase4SecurityTest.php               # XSS, SQL injection
│       ├── Phase5LoggingTest.php                # Audit logging
│       ├── Phase6DependencyTest.php             # Dependency audit
│       ├── Phase7PenetrationTest.php            # Penetration test
│       ├── ProfileTest.php                      # Profile CRUD
│       ├── RiskFreeDeclarationTest.php          # Deklarasi nihil
│       └── RiskReportControllerTest.php         # CRUD laporan risiko
│
├── .env.example                                 # Template environment
├── composer.json                                # PHP dependencies
├── package.json                                 # Node dependencies
├── tailwind.config.js                           # Tailwind config
├── vite.config.js                               # Vite bundler
├── phpunit.xml                                  # PHPUnit config
└── Dockerfile                                   # Container setup
```

---

## 3. DATABASE SCHEMA

### 📊 Entity Relationship Diagram (Text-based)

```
branches
  ├── id (PK, BIGINT)
  ├── kode_cabang (VARCHAR, unique)
  ├── nickname_cabang (VARCHAR, nullable)
  ├── nama_cabang (VARCHAR)
  ├── is_active (BOOLEAN, default true)
  ├── korwil_id (FK → users.id, nullable)
  └── created_at, updated_at

users
  ├── id (PK, BIGINT)
  ├── name (VARCHAR)
  ├── username (VARCHAR, unique)
  ├── email (VARCHAR, unique)
  ├── password (VARCHAR)
  ├── branch_id (FK → branches.id)
  ├── is_active (BOOLEAN, default true)
  ├── password_changed_at (DATETIME, nullable)
  └── timestamps

roles (Spatie)
  ├── id (PK, BIGINT)
  ├── name (VARCHAR, unique)
  ├── guard_name (VARCHAR)
  ├── role_category (ENUM: maker,checker,viewer,admin) ← BARU!
  └── created_at, updated_at

model_has_roles (Spatie Pivot)
  ├── role_id (FK → roles.id)
  ├── model_type (VARCHAR)
  └── model_id (BIGINT → users.id)

permissions (Spatie)
  ├── id (PK, BIGINT)
  ├── name (VARCHAR, unique)
  ├── guard_name (VARCHAR)
  └── created_at, updated_at

role_has_permissions (Spatie Pivot)
  ├── permission_id (FK → permissions.id)
  └── role_id (FK → roles.id)

risk_items
  ├── id (PK, BIGINT)
  ├── nama_risiko (VARCHAR)
  ├── kategori (ENUM: finansial,non-finansial)
  ├── sumber_risiko (ENUM: manusia,proses_internal,sistem_teknologi,faktor_eksternal)
  ├── role_target (ENUM: teller,ca,csr,security,kacab,korwil)
  └── timestamps

risk_causes
  ├── id (PK, BIGINT)
  ├── risk_item_id (FK → risk_items.id)
  ├── penyebab (VARCHAR)
  ├── sumber_risiko (ENUM: manusia,proses_internal,sistem_teknologi,faktor_eksternal)
  └── timestamps

risk_mitigations
  ├── id (PK, BIGINT)
  ├── risk_cause_id (FK → risk_causes.id)
  ├── mitigasi (VARCHAR)
  └── timestamps

risk_reports
  ├── id (PK, BIGINT)
  ├── kode_laporan (VARCHAR, unique) ← format: RISK-{cabang}{role}-{YYYYMM}-{0001}
  ├── user_id (FK → users.id)
  ├── branch_id (FK → branches.id)
  ├── kategori (ENUM: finansial,non-finansial)
  ├── tanggal_kejadian (DATE)
  ├── tanggal_diketahui (DATE)
  ├── risk_item_id (FK → risk_items.id, nullable)
  ├── other_item_description (VARCHAR, nullable)
  ├── risk_cause_id (FK → risk_causes.id, nullable)
  ├── other_cause_description (VARCHAR, nullable)
  ├── kronologis_kejadian (TEXT)
  ├── mitigasi_tambahan (TEXT, nullable)
  ├── durasi_penyelesaian (INT, nullable)
  ├── durasi_satuan (ENUM: menit,jam,hari,minggu,bulan, nullable)
  ├── dampak_finansial (DECIMAL 15,2, default 0)
  ├── dampak_non_finansial (TEXT, nullable)
  ├── skala_dampak (VARCHAR, nullable)
  ├── approval_status (ENUM: pending,pending_kacab,pending_korwil,pending_revision,approved,rejected,need_revision)
  ├── resolution_status (ENUM: open,in_progress,closed, default: open)
  ├── revision_note (TEXT, nullable)
  └── timestamps

risk_report_logs
  ├── id (PK, BIGINT)
  ├── risk_report_id (FK → risk_reports.id)
  ├── user_id (FK → users.id)
  ├── note (TEXT)
  ├── status_after_note (VARCHAR)
  ├── old_data (JSON/TEXT, nullable) ← snapshot sebelum update
  └── timestamps

notifications
  ├── id (PK, BIGINT)
  ├── user_id (FK → users.id)
  ├── risk_report_id (FK → risk_reports.id, nullable)
  ├── type (VARCHAR) ← new_report, approved, rejected, revision_requested, revision_submitted, closed, declaration, declaration_violated
  ├── message (TEXT)
  ├── is_read (BOOLEAN, default false)
  └── timestamps

risk_free_declarations
  ├── id (PK, BIGINT)
  ├── branch_id (FK → branches.id)
  ├── user_id (FK → users.id)
  ├── periode (TINYINT: 1 atau 2)
  ├── bulan (TINYINT: 1-12)
  ├── tahun (YEAR)
  ├── statement_text (TEXT)
  ├── status (ENUM: active,violated, default: active)
  ├── violated_at (DATETIME, nullable)
  ├── violated_by (FK → users.id, nullable)
  └── timestamps

risk_free_declaration_details
  ├── id (PK, BIGINT)
  ├── risk_free_declaration_id (FK → risk_free_declarations.id)
  ├── jabatan (VARCHAR)
  ├── is_clean (BOOLEAN)
  ├── keterangan (TEXT, nullable)
  └── timestamps
```

### 🔗 Relasi Antar Tabel (Visual)

```
branches ──┬── users (branch_id)
           ├── risk_reports (branch_id)
           ├── risk_free_declarations (branch_id)
           └── korwil_id → users.id

users ──┬── risk_reports (user_id)
        ├── risk_report_logs (user_id)
        ├── notifications (user_id)
        ├── risk_free_declarations (user_id)
        ├── risk_free_declarations (violated_by)
        └── branches (korwil_id)

roles ──┬── model_has_roles (role_id → user)
        └── role_has_permissions (role_id → permission)

risk_items ──┬── risk_reports (risk_item_id)
             └── risk_causes (risk_item_id)

risk_causes ──┬── risk_reports (risk_cause_id)
              └── risk_mitigations (risk_cause_id)

risk_reports ──┬── risk_report_logs (risk_report_id)
               └── notifications (risk_report_id)

risk_free_declarations ──┬── risk_free_declaration_details
                         └── notifications (via controller)
```

---

## 4. USER FUNCTIONS (MODEL)

Semua fungsi helper ada di `app/Models/User.php`:

### 🔍 Role Category Helpers

```php
// Ambil role_category dari ROLE (bukan dari kolom user)
public function roleCategory(): ?string
{
    $role = $this->roles->first();
    return $role?->role_category ?? null;
}

// Cek apakah user bisa bikin laporan (maker + checker)
public function isMaker(): bool
{
    $cat = $this->roleCategory();
    return $cat === 'maker' || $cat === 'checker';
}

// Cek apakah user checker murni (kacab)
public function isChecker(): bool
{
    return $this->roleCategory() === 'checker';
}

// Cek apakah user viewer (korwil)
public function isViewer(): bool
{
    return $this->roleCategory() === 'viewer';
}

// Cek apakah user admin (manrisk)
public function isAdmin(): bool
{
    return $this->roleCategory() === 'admin';
}

// Cek apakah user bisa bikin laporan
public function canCreateReport(): bool
{
    return in_array($this->roleCategory(), ['maker', 'checker']);
}
```

### 👤 Relasi & Utility

```php
// Relasi ke cabang
public function branch()
{
    return $this->belongsTo(Branch::class);
}

// Relasi ke cabang yang diawasi (khusus korwil)
public function supervisedBranches()
{
    return $this->hasMany(Branch::class, 'korwil_id');
}

// Ambil nama role pertama
public function primaryRoleName(): ?string
{
    $role = $this->getRoleNames()->first();
    return $role ? (string) $role : null;
}

// Cek password expired (>90 hari)
public function mustChangePassword(): bool
{
    if (is_null($this->password_changed_at)) return true;
    return $this->password_changed_at->addDays(90)->isPast();
}
```

### 📋 Mapping Role → Role Category

| Role      | role_category | isMaker | isChecker  | isViewer | isAdmin  | canCreateReport |
|-----------|:-------------:|:-------:|:----------:|:--------:|:--------:|:---------------:|
| teller    | maker         | ✅      | ❌        | ❌       | ❌      | ✅              |
| ca        | maker         | ✅      | ❌        | ❌       | ❌      | ✅              |
| csr       | maker         | ✅      | ❌        | ❌       | ❌      | ✅              |
| security  | maker         | ✅      | ❌        | ❌       | ❌      | ✅              |
| kacab     | checker       | ✅      | ✅        | ❌       | ❌      | ✅              |
| korwil    | viewer        | ❌      | ❌        | ✅       | ❌      | ❌              |
| manrisk   | admin         | ❌      | ❌        | ❌       | ✅      | ❌              |

---

## 5. ROUTE FUNCTIONS (DETAIL)

### 🔐 Auth Routes (`routes/auth.php`)

| Method | URI | Controller@Method | Middleware | Rate Limit | Keterangan |
|--------|-----|-------------------|-----------|------------|------------|
| GET | `/login` | AuthenticatedSessionController@create | guest | - | Halaman login |
| POST | `/login` | AuthenticatedSessionController@store | guest | **5/menit** | Proses login (brute force protection) |
| POST | `/logout` | AuthenticatedSessionController@destroy | auth | - | Logout |
| GET | `/register` | RegisteredUserController@create | guest | - | Halaman register |
| POST | `/register` | RegisteredUserController@store | guest | - | Proses register |
| GET | `/forgot-password` | PasswordResetLinkController@create | guest | - | Lupa password |
| POST | `/forgot-password` | PasswordResetLinkController@store | guest | - | Kirim email reset |
| GET | `/reset-password/{token}` | NewPasswordController@create | guest | - | Form reset password |
| POST | `/reset-password` | NewPasswordController@store | guest | - | Proses reset |
| GET | `/verify-email` | EmailVerificationPromptController | auth | - | Notif verifikasi |
| GET | `/verify-email/{id}/{hash}` | VerifyEmailController | auth, signed | 6/menit | Verifikasi email |
| POST | `/email/verification-notification` | EmailVerificationNotificationController | auth | 6/menit | Kirim ulang verifikasi |
| GET | `/confirm-password` | ConfirmablePasswordController@show | auth | - | Konfirmasi password |
| POST | `/confirm-password` | ConfirmablePasswordController@store | auth | - | Proses konfirmasi |
| PUT | `/password` | PasswordController@update | auth | - | Update password |

### 👤 User Routes (Login Required) — `routes/web.php` (bagian auth)

| Method | URI | Controller@Method | Middleware | Rate Limit | Akses | Keterangan |
|--------|-----|-------------------|-----------|------------|-------|------------|
| GET | `/` | Closure (redirect) | - | - | Semua | Redirect ke /login |
| GET | `/dashboard` | Closure (inline) | auth, verified | - | Semua role | Dashboard + chart |
| GET | `/profile` | ProfileController@edit | auth | - | Semua | Edit profile |
| POST/PATCH | `/profile` | ProfileController@update | auth | - | Semua | Update profile |
| GET | `/form-risiko/{kategori}` | RiskReportController@create | auth | - | **maker, checker** | Form laporan (finansial/non-finansial) |
| POST | `/form-risiko` | RiskReportController@store | auth | **10/menit** | **maker, checker** | Simpan laporan baru |
| GET | `/review-laporan` | RiskReportController@review | auth | - | **checker** | Review & approve |
| POST | `/risk-reports/{id}/status` | RiskReportController@updateStatus | auth | **10/menit** | **checker** | Approve/reject laporan |
| POST | `/risk-reports/{id}/resolution` | RiskReportController@updateResolution | auth | **10/menit** | **maker, checker** | Update status tindak lanjut |
| GET | `/riwayat-risiko` | RiskReportController@index | auth | - | **Semua** | Riwayat/monitoring (discope per role) |
| GET | `/risk-report/{id}` | RiskReportController@show | auth | - | **Semua** (discope) | Detail laporan |
| POST | `/risk-report/{id}/progress` | RiskReportController@addProgress | auth | **10/menit** | **maker, checker** | Tambah catatan progress |
| POST | `/risk-report/{id}/request-revision` | RiskReportController@requestRevision | auth | **10/menit** | **manrisk only** | Minta revisi |
| POST | `/risk-report/{id}/submit-revision` | RiskReportController@submitRevision | auth | **10/menit** | **maker, checker** | Kirim revisi |
| POST | `/risk-report/{id}/approve-revision` | RiskReportController@approveRevision | auth | **10/menit** | **manrisk only** | Setujui revisi |
| GET | `/notifications` | NotificationController@index | auth | - | **Semua** | Daftar notifikasi |
| POST | `/notifications/mark-all-read` | NotificationController@markAllRead | auth | - | **Semua** | Tandai semua dibaca |
| GET | `/notifications/{id}/read` | NotificationController@markAsRead | auth | - | **Semua** | Baca notifikasi + redirect |
| GET | `/notifications/unread-count` | NotificationController@unreadCount | auth | - | **Semua** | JSON jumlah notif belum dibaca |
| GET | `/export-risiko` | ExportRiskReportController@export | auth | - | **Semua** (discope) | Export CSV |
| GET | `/deklarasi-nihil` | RiskFreeDeclarationController@create | auth | - | **checker** | Form deklarasi nihil |
| POST | `/deklarasi-nihil` | RiskFreeDeclarationController@store | auth | **10/menit** | **checker** | Simpan deklarasi |
| GET | `/deklarasi-nihil/riwayat` | RiskFreeDeclarationController@history | auth | - | **checker, viewer, admin** | Riwayat deklarasi |

### 🛡️ Admin Routes (ManRisk Only) — `routes/web.php` (bagian admin)

| Method | URI | Controller@Method | Middleware | Keterangan |
|--------|-----|-------------------|-----------|------------|
| GET | `/admin/users` | AdminUserController@index | auth, role:manrisk | Daftar user |
| POST | `/admin/users` | AdminUserController@store | auth, role:manrisk | Tambah user |
| PATCH | `/admin/users/{user}` | AdminUserController@update | auth, role:manrisk | Update user |
| POST | `/admin/users/{user}/toggle-status` | AdminUserController@toggleStatus | auth, role:manrisk | Aktif/nonaktifkan user |
| GET | `/admin/risk-master` | RiskMasterController@index | auth, role:manrisk | Master data risiko |
| POST | `/admin/risk-master/item` | RiskMasterController@storeItem | auth, role:manrisk | Tambah item risiko |
| POST | `/admin/risk-master/item/{id}/cause` | RiskMasterController@storeCause | auth, role:manrisk | Tambah penyebab |
| DELETE | `/admin/risk-master/item/{id}` | RiskMasterController@destroyItem | auth, role:manrisk | Hapus item |
| PATCH | `/admin/risk-master/cause/{id}` | RiskMasterController@updateCause | auth, role:manrisk | Update penyebab |
| POST | `/admin/risk-master/cause/{causeId}/mitigation` | RiskMasterController@storeMitigation | auth, role:manrisk | Tambah mitigasi |
| GET | `/admin/roles` | RoleController@index | auth, role:manrisk | Manajemen role |
| POST | `/admin/roles` | RoleController@store | auth, role:manrisk | Tambah role |
| PATCH | `/admin/roles/{role}` | RoleController@update | auth, role:manrisk | Update role |
| DELETE | `/admin/roles/{role}` | RoleController@destroy | auth, role:manrisk | Hapus role |
| GET | `/branches-management` | BranchManagementController@index | auth, role:manrisk | Manajemen cabang |
| PUT | `/branches-management/{id}` | BranchManagementController@update | auth, role:manrisk | Update cabang |
| POST | `/branches-management` | BranchManagementController@store | auth, role:manrisk | Tambah cabang |
| POST | `/deklarasi-nihil/{id}/violate` | RiskFreeDeclarationController@violate | auth, role:manrisk | Violate deklarasi |

---

## 6. API ENDPOINTS

Aplikasi ini **tidak punya REST API publik**. Semua endpoint adalah internal (Blade-to-Controller).

### Satu-satunya endpoint JSON:

| Method | URI | Response | Keterangan |
|--------|-----|----------|------------|
| GET | `/notifications/unread-count` | `{"count": 5}` | Dipanggil tiap 30 detik oleh Alpine.js di sidebar |

### Export CSV (Streamed Response):

| Method | URI | Response Type | Keterangan |
|--------|-----|---------------|------------|
| GET | `/export-risiko` | `text/csv` | Download CSV dengan filter |

---

## 7. ROLES & ROLE CATEGORIES

### 🎭 Definisi Role Category

```
ROLE CATEGORY = Level akses sistem yang ditentukan oleh ROLE (bukan per-user)
```

| Category    | Kode  | Bisa Buat Laporan | Bisa Approve        | Bisa Lihat Semua    | Bisa Kelola Sistem | Role yang Termasuk        |
|-------------|:-----:|:-----------------:|:-------------------:|:-------------------:|:------------------:|:-------------------------:|
| **maker**   | M     | ✅               | ❌                  | ❌ (hanya sendiri) | ❌                 | teller, ca, csr, security |
| **checker** | C     | ✅               | ✅ (cabang sendiri) | ❌ (hanya cabang)  | ❌                 | kacab                     |
| **viewer**  | V     | ❌               | ❌                  | ✅ (cabang diawasi)| ❌                 | korwil                    |
| **admin**   | A     | ❌               | ❌                  | ✅ (semua cabang)  | ✅                 | manrisk                   |

### 📋 Policy Matrix (RiskReportPolicy)

| Policy Method       | maker           | checker                  | viewer              | admin             |
|---------------------|:---------------:|:------------------------:|:-------------------:|:-----------------:|
| `view()`            | Own report only | Own branch               | Supervised branches | All reports       |
| `approve()`         | ❌             | ✅ (own branch, pending) | ❌                 | ❌                |
| `updateProgress()`  | ✅ (own report)| ✅ (own branch)          | ❌                 | ❌                |
| `close()`           | ❌             | ✅ (own branch)          | ❌                 | ❌                |
| `requestRevision()` | ❌             | ❌                       | ❌                 | ✅ (manrisk only) |
| `submitRevision()`  | ✅ (own report)| ✅ (own branch)          | ❌                 | ❌                |
| `approveRevision()` | ❌             | ❌                       | ❌                 | ✅ (manrisk only) |
| `export()`          | ✅ (own)       | ✅ (own branch)          | ✅ (supervised)    | ✅ (all)          |

### 🧩 Spatie Permission Roles

```
7 Role Bawaan:
├── manrisk   → role_category: admin   → Akses penuh sistem
├── korwil    → role_category: viewer  → Pantau wilayah
├── kacab     → role_category: checker → Approve & tindak lanjut
├── teller    → role_category: maker   → Buat laporan
├── ca        → role_category: maker   → Buat laporan
├── csr       → role_category: maker   → Buat laporan
└── security  → role_category: maker   → Buat laporan

Role Kustom (bisa ditambah via Manajemen Role):
└── (nama bebas) → role_category: [maker|checker|viewer|admin]
```

---

## 8. FLOW BISNIS

### 📝 Alur Lengkap Laporan Risiko

```
STEP 1: LAPORAN DIBUAT (Maker)
┌─────────────────────────────────────────────────────────────────────┐
│  Teller/CA/CSR/Security/Kacab mengisi form risiko                   │
│  → Pilih kategori (finansial/non-finansial)                         │
│  → Pilih item risiko dari bank soal (atau isi manual)               │
│  → Isi kronologis (min 20 kata), dampak, mitigasi                   │
│  → Submit                                                           │
│                                                                     │
│  🔹 Jika pelapor = Kacab → auto-approved                            │
│  🔹 Jika pelapor = Staff → status: pending_kacab                    │
│  🔹 Notifikasi dikirim ke Kacab cabang terkait                      │
└─────────────────────────────────────────────────────────────────────┘
                          │
                          ▼
STEP 2: REVIEW & APPROVAL (Checker / Kacab)
┌─────────────────────────────────────────────────────────────────────┐
│  Kacab buka menu "Review & Tindak Lanjut"                           │
│  → Lihat daftar laporan pending dari cabangnya                      │
│  → Klik detail untuk lihat lengkap                                  │
│                                                                     │
│  ╔═══════════════════════════════════════════════════════════════╗  │
│  ║  APPROVE ✅ → status: approved, laporan masuk tindak lanjut   ║  │
│  ║  REJECT ❌ → status: need_revision, kirim catatan ke maker    ║  │
│  ╚═══════════════════════════════════════════════════════════════╝  │
│                                                                     │
│  🔹 Notifikasi dikirim ke pembuat laporan                           │
└─────────────────────────────────────────────────────────────────────┘
                          │
                          ▼
STEP 3: TINDAK LANJUT (Maker & Checker)
┌─────────────────────────────────────────────────────────────────────┐
│  Laporan yang sudah approved masuk ke daftar "Tindak Lanjut"        │
│                                                                     │
│  Maker:                                                             │
│  → Tambah catatan progress (note + status)                          │
│  → Tidak bisa close laporan                                         │
│                                                                     │
│  Kacab:                                                             │
│  → Update resolution status: open → in_progress → closed            │
│  → Bisa close laporan (hanya cabang sendiri)                        │
│                                                                     │
│  🔹 Notifikasi ke maker saat laporan di-closed                      │
└─────────────────────────────────────────────────────────────────────┘
                          │
                          ▼
STEP 4: MONITORING (Semua Role — discope)
┌─────────────────────────────────────────────────────────────────────┐
│  Setiap role bisa lihat riwayat dengan cakupan berbeda:             │
│                                                                     │
│  Maker   → hanya laporan sendiri                                    │
│  Checker → semua laporan cabangnya                                  │
│  Viewer  → laporan cabang yang diawasi (korwil)                     │
│  Admin   → semua laporan semua cabang (manrisk)                     │
│                                                                     │
│  Fitur: search, filter kategori, filter cabang, filter tanggal,     │
│          filter status, sorting, pagination, export CSV             │
└─────────────────────────────────────────────────────────────────────┘

### 🔄 Alur Revisi Laporan

```
Laporan Approved (oleh Kacab)
        │
        ▼
ManRisk minta revisi → status: need_revision
        │
        ▼
Maker/Kacab submit revisi → status: pending_revision (atau pending_kacab jika dari Kacab)
        │
        ▼
ManRisk approve revisi → status: approved kembali
        │
        ▼
Lanjut ke tindak lanjut seperti biasa
```

### 📅 Alur Deklarasi Nihil Risiko

```
Periode 1 (tgl 1-14) atau Periode 2 (tgl 15-akhir)
        │
        ▼
Kacab buka form deklarasi nihil
  → Pilih jabatan yang "clean" (Teller, CA, CS, Security, Kacab)
  → Isi pernyataan (statement_text)
  → Submit
        │
        ▼
Notifikasi dikirim ke ManRisk
        │
        ▼
ManRisk bisa "violate" jika ternyata ada laporan risiko di periode itu
  → Status deklarasi berubah jadi "violated"
  → Notifikasi dikirim ke Kacab
```

---

## 9. SECURITY FEATURES

### 🛡️ Security Headers (Global Middleware: `SecurityHeaders.php`)

| Header | Value | Fungsi |
|--------|-------|--------|
| `X-Content-Type-Options` | `nosniff` | Cegah MIME sniffing |
| `X-Frame-Options` | `DENY` | Cegah clickjacking |
| `X-XSS-Protection` | `1; mode=block` | Proteksi XSS (legacy) |
| `Referrer-Policy` | `strict-origin-when-cross-origin` | Kontrol referrer |
| `Permissions-Policy` | `geolocation=(), microphone=(), camera=()` | Batasi API browser |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` | Paksa HTTPS |

### 🔒 XSS Protection

```
Semua input teks di-strip_tags() sebelum disimpan:
├── StoreRiskReportRequest.php:
│   ├── kronologis_kejadian
│   ├── mitigasi_tambahan
│   ├── tindakan_awal
│   ├── other_item_description
│   ├── other_cause_description
│   └── dampak_non_finansial
│
├── UpdateRiskApprovalStatusRequest.php:
│   ├── alasan_reject
│   └── alasan_revisi
│
└── RiskReportController@requestRevision:
    └── revision_note
```

### 🚦 Rate Limiting

| Endpoint                                  | Limit        | Tujuan                 |
|-------------------------------------------|--------------|------------------------|
| `POST /login`                             | 5 per menit  | Brute force protection |
| `POST /form-risiko`                       | 10 per menit | Spam form protection   |
| `POST /risk-reports/{id}/status`          | 10 per menit | Spam approve/reject    |
| `POST /risk-reports/{id}/resolution`      | 10 per menit | Spam update status     |
| `POST /risk-report/{id}/progress`         | 10 per menit | Spam add progress      |
| `POST /risk-report/{id}/request-revision` | 10 per menit | Spam request revision  |
| `POST /risk-report/{id}/submit-revision`  | 10 per menit | Spam submit revision   |
| `POST /risk-report/{id}/approve-revision` | 10 per menit | Spam approve revision  |
| `POST /deklarasi-nihil`                   | 10 per menit | Spam declaration       |
| `GET /verify-email/{id}/{hash}`           | 6 per menit  | Spam verification      |
| `POST /email/verification-notification`   | 6 per menit  | Spam resend            |

### 🔐 Password Policy

| Aturan         | Detail                                                    |
|----------------|-----------------------------------------------------------|
| Minimum length | 8 karakter                                                |
| Complexity     | Wajib: huruf besar, huruf kecil, angka, simbol            |
| Expiry         | 90 hari (via `password_changed_at` column)                |
| Redirect       | User dengan password expired diarahkan ke halaman profile |
| Hashing        | Bcrypt via Laravel `Hash::make()`                         |

### 🧪 SQL Injection Protection

```
Semua query menggunakan Eloquent ORM → parameter binding otomatis
Tidak ada raw SQL queries
Search menggunakan LIKE dengan parameter binding
```

### 🚫 CSRF Protection

```
Semua POST/PUT/PATCH/DELETE request dilindungi CSRF token
Token dikirim via meta tag <meta name="csrf-token">
Laravel VerifyCsrfToken middleware aktif
```

---

## 10. TESTING

### 📊 Ringkasan Test Suite

| Fase | File                               | Jumlah Test | Fokus                                                     |
|:----:|------------------------------------|:-----------:|-----------------------------------------------------------|
| 1    | `Phase1ConfigTest.php`             |      13     | Security config audit (debug, session, cors, headers)     |
| 2    | `Phase2AuthTest.php`               |      12     | Auth security (rate limit, password policy, lockout, CSRF)|
| 3    | `Phase3AuthorizationTest.php`      |      41     | Authorization per role (view, approve, close, revision)   |
| 4    | `Phase4SecurityTest.php`           |      20     | XSS stripping, SQL injection safety                       |
| 5    | `Phase5LoggingTest.php`            |      17     | Audit logging (create, approve, reject, revision, export) |
| 6    | `Phase6DependencyTest.php`         |      21     | Dependency audit (composer, npm, security headers)        |
| 7    | `Phase7PenetrationTest.php`        |      12     | Penetration test (cross-branch, mass assignment, throttle)|
| 8    | `Phase8E2ETest.php` (Browser/Dusk) |      -      | End-to-end browser test                                   |
| -    | `RiskReportControllerTest.php`     |      58     | CRUD laporan risiko                                       |
| -    | `RiskFreeDeclarationTest.php`      |      21     | Deklarasi nihil risiko                                    |
| -    | `DashboardTest.php`                |      8      | Dashboard per role                                        |
| -    | `MasterRiskControllerTest.php`     |      31     | CRUD master data risiko                                   |
| -    | `ProfileTest.php`                  |      5      | Profile CRUD                                              |
|      | **TOTAL**                          |     272✅ (2⚠️skipped)                                                 |

### 🧪 Cara Menjalankan Test

```bash
# Semua feature tests
php artisan test --testsuite=Feature

# Test spesifik
php artisan test tests/Feature/RiskReportControllerTest.php

# Browser test (Dusk)
php artisan dusk tests/Browser/Phase8E2ETest.php

# Dengan coverage (Xdebug required)
php artisan test --coverage
```

### ⚙️ Test Database

```
Database: SQLite :memory: (in-memory)
Migrations: Semua 28 migration dijalankan dari awal
Seeding: Dilakukan manual di setUp() tiap test class
Factory: UserFactory, BranchFactory, RiskReportFactory, dll.
```

---

## 11. ENVIRONMENT & CONFIG

### 📄 .env Variables Penting

```env
# Database
DB_CONNECTION=mysql           # Production: mysql, Testing: sqlite
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=form_risk
DB_USERNAME=root
DB_PASSWORD=

# App
APP_NAME="BPR Reporting"
APP_ENV=production            # Production: production, Local: local
APP_DEBUG=false               # WAJIB false di production!
APP_URL=https://example.com

# Session
SESSION_DRIVER=file           # Bisa diganti database/cookie
SESSION_LIFETIME=120          # 120 menit
SESSION_SECURE_COOKIE=true    # WAJIB true di production (HTTPS)
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# CORS
CORS_ALLOWED_ORIGINS=https://example.com  # Jangan pake wildcard (*) di production
```

### 🔄 Database Connection Switching

```
┌──────────────────────────────────────────────────────────────────────┐
│  Testing (phpunit.xml)                     Production (.env)         │
│                                                                      │
│  DB_CONNECTION=sqlite                       DB_CONNECTION=mysql      │ 
│  DB_DATABASE=:memory:                       DB_DATABASE=form_risk    │
│                                                                      │
│  Kelebihan:                                Kelebihan:                │
│  ✅ Cepat (in-memory)                      ✅ Persistent data       │
│  ✅ Isolasi tiap test                      ✅ Full MySQL features   │
│  ✅ Auto reset                             ✅ Relationships work    │
│                                                                      │
│  Kekurangan:                               Kekurangan:               │
│  ❌ Tidak semua MySQL features              ❌ Lebih lambat         │
│  ❌ Harus migrate tiap test                 ❌ Perlu setup database │
└──────────────────────────────────────────────────────────────────────┘
```

### 📦 Dependencies

```json
// composer.json — PHP
{
    "laravel/framework": "^11.0",
    "spatie/laravel-permission": "^6.0",
    "spatie/laravel-ignition": "^2.0",
    "laravel/dusk": "^8.0"  // Browser testing
}

// package.json — Node
{
    "tailwindcss": "^3.4",
    "alpinejs": "^3.13",
    "vite": "^5.0",
    "chart.js": "^4.4",     // Dashboard charts
    "axios": "^1.6"
}
```

### 🐳 Docker

```dockerfile
# Dockerfile — Multi-stage build
FROM php:8.2-cli
# Install dependencies, composer, node
# Copy application
# Run artisan serve
```

---

## 🏁 REVISION HISTORY

| Tanggal | Versi | Perubahan |
|---------|:-----:|-----------|
| 2026-05-12 | 1.0 | Initial documentation |
| 2026-05-12 | 1.1 | Added role_category to roles table (moved from users) |
| 2026-05-12 | 1.2 | Added admin role_category for manrisk |
| 2026-05-12 | 1.3 | Fixed sidebar for admin menu access |

---

> **Dibuat oleh:** BPR Dev Team  
> **Last Updated:** 12 Mei 2026  
> **Total Tests:** 272 ✅ · 2 ⚠️ skipped  
> **File:** `PROJECT_REFERENCE.md`
