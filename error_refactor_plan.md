# ERROR & PROBLEM REFACTOR PLAN

Dokumentasi error dan masalah yang ditemukan selama refactoring FASE 1-12 (Hardcoded Role Names → Role Category).

---

## FASE 1: Migration role_target di risk_items

### ERROR 1.1: SQLite MODIFY COLUMN not supported
- **File:** `database/migrations/2026_05_12_120000_change_role_target_in_risk_items.php`
- **Baris:** ~15-20 (Schema::table → change())
- **Fungsi:** Mengubah kolom `role_target` dari ENUM ke VARCHAR(50)
- **Error:** SQLite tidak mendukung `MODIFY COLUMN` / `change()`
- **Solusi:** Ditambahkan guard `if (DB::getDriverName() !== 'sqlite')` untuk skip schema change di environment test (SQLite), dan hanya jalanin data update.

---

## FASE 3: RiskReportController@create

### ERROR 3.1: Filter role_target masih pake role name
- **File:** `app/Http/Controllers/RiskReportController.php`
- **Baris:** ~43-46
- **Fungsi:** `create()` — filter risk items berdasarkan role user
- **Masalah:** Masih pake `$userRole = $this->primaryRoleName()` yang return role name (e.g. 'teller'), bukan role_category
- **Solusi:** Diubah jadi `Auth::user()->roleCategory()` — filter pake 'maker', 'checker', 'viewer'

---

## FASE 4: RiskReportPolicy

### ERROR 4.1: hasRole('manrisk') masih hardcoded
- **File:** `app/Policies/RiskReportPolicy.php`
- **Baris:** 95 dan 122
- **Fungsi:** `approve()` dan `approveRevision()` — ngecek apakah user adalah admin
- **Masalah:** `$user->hasRole('manrisk')` — hardcoded role name
- **Solusi:** Diubah jadi `$user->isAdmin()`

---

## FASE 5: RiskFreeDeclarationController

### ERROR 5.1: User::role('manrisk') hardcoded
- **File:** `app/Http/Controllers/RiskFreeDeclarationController.php`
- **Baris:** 147
- **Fungsi:** Query user dengan role manrisk
- **Masalah:** `User::role('manrisk')->get()` — hardcoded
- **Solusi:** Diubah jadi query `whereHas('roles', fn($q) => $q->where('role_category', 'admin'))`

### ERROR 5.2: hasRole('manrisk') hardcoded
- **File:** `app/Http/Controllers/RiskFreeDeclarationController.php`
- **Baris:** 166
- **Fungsi:** Cek apakah user adalah admin
- **Masalah:** `$user->hasRole('manrisk')`
- **Solusi:** Diubah jadi `$user->isAdmin()`

### ERROR 5.3: User::role('kacab') hardcoded
- **File:** `app/Http/Controllers/RiskFreeDeclarationController.php`
- **Baris:** 194
- **Fungsi:** Query user checker di cabang tertentu
- **Masalah:** `User::role('kacab')->where('branch_id', ...)`
- **Solusi:** Diubah jadi `User::whereHas('roles', fn($q) => $q->where('role_category', 'checker'))->where('branch_id', ...)`

---

## FASE 6: RiskReportController Notifikasi

### ERROR 6.1: User::role('kacab') hardcoded (line 126)
- **File:** `app/Http/Controllers/RiskReportController.php`
- **Baris:** 126
- **Fungsi:** Mencari user checker untuk notifikasi
- **Masalah:** `User::role('kacab')->where('branch_id', ...)`
- **Solusi:** Diubah jadi `User::whereHas('roles', fn($q) => $q->where('role_category', 'checker'))->where('branch_id', ...)`

### ERROR 6.2: User::role('kacab') hardcoded (line 528)
- **File:** `app/Http/Controllers/RiskReportController.php`
- **Baris:** 528
- **Fungsi:** Sama, notifikasi untuk checker
- **Masalah:** Sama — hardcoded 'kacab'
- **Solusi:** Sama — diubah ke role_category='checker'

### ERROR 6.3: User::role('manrisk') hardcoded (line 540)
- **File:** `app/Http/Controllers/RiskReportController.php`
- **Baris:** 540
- **Fungsi:** Mencari user admin untuk notifikasi
- **Masalah:** `User::role('manrisk')->get()`
- **Solusi:** Diubah jadi query `whereHas('roles', fn($q) => $q->where('role_category', 'admin'))`

---

## FASE 7: Routes web.php

### ERROR 7.1: Branch filter pake role name 'korwil'
- **File:** `routes/web.php`
- **Baris:** 31-42
- **Fungsi:** Filter cabang berdasarkan role user di dashboard
- **Masalah:** `if ($role === 'korwil')` — hardcoded
- **Solusi:** Diubah jadi `if ($roleCategory === 'viewer')` — tapi masih ada nested check `$role === 'korwil'` untuk bedain viewer biasa vs korwil

### ERROR 7.2: Report query pake role name 'korwil'
- **File:** `routes/web.php`
- **Baris:** 71-74
- **Fungsi:** Filter report query di dashboard
- **Masalah:** `if ($role === 'korwil')` — hardcoded
- **Solusi:** Diubah jadi `if ($roleCategory === 'viewer')`

### ERROR 7.3: Admin check pake role name 'manrisk'
- **File:** `routes/web.php`
- **Baris:** 283
- **Fungsi:** Ringkasan wilayah khusus admin
- **Masalah:** `if ($role === 'manrisk')` — hardcoded
- **Solusi:** Diubah jadi `if ($roleCategory === 'admin')`

### ERROR 7.4: Route middleware closure ga bisa dipake di group
- **File:** `routes/web.php`
- **Baris:** 493-498
- **Fungsi:** Middleware untuk route group admin
- **Masalah:** Closure middleware `Route::middleware(['auth', function($request, $next) { ... }])` error: `Object of class Closure could not be converted to string`
- **Penyebab:** Laravel `RouteRegistrar::attribute('middleware')` mencoba cast middleware ke string, tapi Closure ga bisa di-cast ke string
- **Status:** BELUM FIX — perlu bikin dedicated Middleware class atau pake pendekatan lain

---

## FASE 8: Layout app.blade.php

### ERROR 8.1: @hasrole('manrisk') hardcoded
- **File:** `resources/views/layouts/app.blade.php`
- **Baris:** 62-72 dan 159-194
- **Fungsi:** Menampilkan menu Dashboard icon dan section Administrasi
- **Masalah:** `@hasrole('manrisk')` — blade directive hardcoded
- **Solusi:** Diubah jadi `@if(Auth::user()->isAdmin())`

---

## FASE 9: Dashboard blade

### ERROR 9.1: @hasanyrole('teller|ca|csr|security') hardcoded
- **File:** `resources/views/dashboard.blade.php`
- **Baris:** 53
- **Fungsi:** Menampilkan section laporan maker
- **Masalah:** `@hasanyrole('teller|ca|csr|security')` — hardcoded semua role maker
- **Solusi:** Diubah jadi `@if(Auth::user()->roleCategory() === 'maker')`

### ERROR 9.2: @hasrole('manrisk') hardcoded (filter dropdown)
- **File:** `resources/views/dashboard.blade.php`
- **Baris:** 136
- **Fungsi:** Filter dropdown cabang
- **Masalah:** `@hasrole('manrisk')`
- **Solusi:** Diubah jadi `@if(Auth::user()->isAdmin())`

### ERROR 9.3: @hasanyrole('kacab|korwil|manrisk') hardcoded
- **File:** `resources/views/dashboard.blade.php`
- **Baris:** 178
- **Fungsi:** Menampilkan chart
- **Masalah:** `@hasanyrole('kacab|korwil|manrisk')`
- **Solusi:** Diubah jadi `@if(Auth::user()->isChecker() || Auth::user()->isViewer() || Auth::user()->isAdmin())`

### ERROR 9.4: @hasanyrole('teller|ca|csr|security|kacab') hardcoded
- **File:** `resources/views/dashboard.blade.php`
- **Baris:** 222
- **Fungsi:** Menampilkan form cards
- **Masalah:** `@hasanyrole('teller|ca|csr|security|kacab')`
- **Solusi:** Diubah jadi `@if(Auth::user()->canCreateReport())`

### ERROR 9.5: @hasrole('kacab') hardcoded (review section)
- **File:** `resources/views/dashboard.blade.php`
- **Baris:** 254
- **Fungsi:** Menampilkan review section
- **Masalah:** `@hasrole('kacab')`
- **Solusi:** Diubah jadi `@if(Auth::user()->isChecker())`

### ERROR 9.6: @hasrole('kacab') hardcoded (deklarasi)
- **File:** `resources/views/dashboard.blade.php`
- **Baris:** 281
- **Fungsi:** Menampilkan deklarasi nihil
- **Masalah:** `@hasrole('kacab')`
- **Solusi:** Diubah jadi `@if(Auth::user()->isChecker())`

### ERROR 9.7: @hasrole('manrisk') hardcoded (ringkasan wilayah)
- **File:** `resources/views/dashboard.blade.php`
- **Baris:** 341
- **Fungsi:** Menampilkan ringkasan wilayah
- **Masalah:** `@hasrole('manrisk')`
- **Solusi:** Diubah jadi `@if(Auth::user()->isAdmin())`

---

## FASE 10: RoleController

### ERROR 10.1: Proteksi role bawaan di update()
- **File:** `app/Http/Controllers/Admin/RoleController.php`
- **Baris:** 42-44 (sebelum dihapus)
- **Fungsi:** Mencegah update role bawaan (manrisk, korwil, kacab, teller, ca, csr, security)
- **Masalah:** `in_array($role->name, ['manrisk', 'korwil', ...])` — hardcoded role names
- **Solusi:** Blok ini dihapus. Semua role bisa diupdate.

### ERROR 10.2: Proteksi role bawaan di destroy()
- **File:** `app/Http/Controllers/Admin/RoleController.php`
- **Baris:** 65-67 (sebelum dihapus)
- **Fungsi:** Mencegah hapus role bawaan
- **Masalah:** `in_array($role->name, ['manrisk', 'korwil', ...])` — hardcoded role names
- **Solusi:** Blok ini dihapus. Yang tersisa hanya `$role->users()->count() > 0` check.

---

## FASE 11: Roles Index Blade

### ERROR 11.1: $isBawaan variable hardcoded
- **File:** `resources/views/admin/roles/index.blade.php`
- **Baris:** 48 (sebelum dihapus)
- **Fungsi:** Nentuin apakah role adalah bawaan sistem
- **Masalah:** `$isBawaan = in_array($role->name, ['manrisk', 'korwil', ...])` — hardcoded
- **Solusi:** Variable dihapus, semua role diperlakukan sama.

### ERROR 11.2: Tipe column (Sistem/Kustom)
- **File:** `resources/views/admin/roles/index.blade.php`
- **Baris:** 81-86 (sebelum dihapus)
- **Fungsi:** Menampilkan badge "Sistem" atau "Kustom"
- **Masalah:** Bedain role berdasarkan hardcoded list
- **Solusi:** Seluruh kolom "Tipe" dihapus.

### ERROR 11.3: Edit/Hapus disembunyikan untuk role bawaan
- **File:** `resources/views/admin/roles/index.blade.php`
- **Baris:** 88-97 (sebelum dihapus)
- **Fungsi:** Sembunyikan tombol Edit/Hapus untuk role bawaan
- **Masalah:** `@if(!$isBawaan)` — diskriminasi role
- **Solusi:** Semua role sekarang tampilkan Edit & Hapus.

---

## FASE 12: ExportRiskReportController

### ERROR 12.1: hasRole('korwil') hardcoded
- **File:** `app/Http/Controllers/ExportRiskReportController.php`
- **Baris:** 29 (sebelum diubah)
- **Fungsi:** Filter cabang untuk viewer (korwil)
- **Masalah:** `if ($user->hasRole('korwil'))` — hardcoded
- **Solusi:** Diubah jadi langsung `Branch::where('korwil_id', $user->id)->pluck('id')` tanpa ngecek role name. Viewer otomatis difilter berdasarkan cabang yang diawasi.

---

## BONUS: BranchManagementController

### ERROR B.1: User::role('korwil') hardcoded
- **File:** `app/Http/Controllers/BranchManagementController.php`
- **Baris:** 38 (sebelum diubah)
- **Fungsi:** Query user viewer (korwil) untuk dropdown
- **Masalah:** `User::role('korwil')` — hardcoded
- **Solusi:** Diubah jadi `User::whereHas('roles', fn($q) => $q->where('role_category', 'viewer'))`

---

## REMAINING ISSUES (BELUM FIX)

### ISSUE A: Route middleware Closure di web.php ✅ FIXED
- **File:** `routes/web.php`
- **Baris:** 493 (sebelum diubah)
- **Fungsi:** Middleware admin untuk route group
- **Masalah:** Closure middleware ga bisa dipake di `Route::middleware()` karena Laravel mencoba cast ke string
- **Solusi:** 
  1. Bikin middleware class `app/Http/Middleware/EnsureAdmin.php` — ngecek `$request->user()->isAdmin()`
  2. Register alias `'admin' => \App\Http\Middleware\EnsureAdmin::class` di `bootstrap/app.php`
  3. Ganti closure di routes jadi `Route::middleware(['auth', 'admin'])`
- **Status:** ✅ FIXED — migrate:fresh --seed sukses

### ISSUE B: Dashboard masih ada sisa hardcoded role name ✅ FIXED
- **File:** `routes/web.php`
- **Baris:** 21 (sebelum diubah)
- **Fungsi:** Dashboard logic
- **Masalah:** Variable `$role = $user?->primaryRoleName()` masih di-fetch dan di-pass ke view, padahal sudah tidak dipake di blade
- **Solusi:** Variable `$role` dihapus dari dashboard route. Semua logic pake `$roleCategory`. Juga dihapus dari `compact()`.
- **Status:** ✅ FIXED

### ISSUE C: PendingCount di dashboard masih pake $role ✅ FIXED
- **File:** `routes/web.php`
- **Baris:** 369 (sebelum diubah)
- **Fungsi:** Hitung badge pending untuk viewer
- **Masalah:** Sebelumnya hybrid `$roleCategory === 'viewer' && $role === 'korwil'`
- **Solusi:** Sekarang pake `$roleCategory === 'viewer'` aja + `$branchIds` yang udah difilter via `Branch::where('korwil_id', $user->id)`. Sudah pure role_category.

---

## PROGRESS TRACKER (Error Fixing)

### ✅ FASE 1-4: SELESAI
| No | Error | Status |
|----|-------|--------|
| 1.1 | SQLite MODIFY COLUMN not supported | ✅ Fixed |
| 3.1 | Filter role_target masih pake role name | ✅ Fixed |
| 4.1 | hasRole('manrisk') masih hardcoded | ✅ Fixed |
| 7.4 | Route middleware Closure ga bisa dipake (ISSUE A) | ✅ Fixed |

### ✅ FASE 5-8: SELESAI
| No | Error | Status |
|----|-------|--------|
| 5.1 | User::role('manrisk') hardcoded | ✅ Fixed |
| 5.2 | hasRole('manrisk') hardcoded | ✅ Fixed |
| 5.3 | User::role('kacab') hardcoded | ✅ Fixed |
| 6.1 | User::role('kacab') hardcoded (line 126) | ✅ Fixed |
| 6.2 | User::role('kacab') hardcoded (line 528) | ✅ Fixed |
| 6.3 | User::role('manrisk') hardcoded (line 540) | ✅ Fixed |
| 7.1 | Branch filter pake role name 'korwil' | ✅ Fixed |
| 7.2 | Report query pake role name 'korwil' | ✅ Fixed |
| 7.3 | Admin check pake role name 'manrisk' | ✅ Fixed |
| 8.1 | @hasrole('manrisk') hardcoded | ✅ Fixed |

### ✅ FASE 9-12: SELESAI
| No | Error | Status |
|----|-------|--------|
| 9.1 | @hasanyrole('teller\|ca\|csr\|security') hardcoded | ✅ Fixed |
| 9.2 | @hasrole('manrisk') hardcoded (filter dropdown) | ✅ Fixed |
| 9.3 | @hasanyrole('kacab\|korwil\|manrisk') hardcoded | ✅ Fixed |
| 9.4 | @hasanyrole('teller\|ca\|csr\|security\|kacab') hardcoded | ✅ Fixed |
| 9.5 | @hasrole('kacab') hardcoded (review section) | ✅ Fixed |
| 9.6 | @hasrole('kacab') hardcoded (deklarasi) | ✅ Fixed |
| 9.7 | @hasrole('manrisk') hardcoded (ringkasan wilayah) | ✅ Fixed |
| 10.1 | Proteksi role bawaan di update() | ✅ Fixed |
| 10.2 | Proteksi role bawaan di destroy() | ✅ Fixed |
| 11.1 | $isBawaan variable hardcoded | ✅ Fixed |
| 11.2 | Tipe column (Sistem/Kustom) | ✅ Fixed |
| 11.3 | Edit/Hapus disembunyikan untuk role bawaan | ✅ Fixed |
| 12.1 | hasRole('korwil') hardcoded | ✅ Fixed |
| B.1 | User::role('korwil') hardcoded | ✅ Fixed |

### ✅ REMAINING ISSUES (Minor) — ALL FIXED
| No | Issue | Status |
|----|-------|--------|
| ISSUE B | Dashboard masih ada sisa hardcoded role name | ✅ Fixed |
| ISSUE C | PendingCount di dashboard masih pake $role | ✅ Fixed |

---

## SUMMARY

| Kategori | Total Error | Fixed | Belum Fix |
|----------|------------|-------|-----------|
| Migration | 1 | 1 | 0 |
| Controller | 8 | 8 | 0 |
| Policy | 2 | 2 | 0 |
| Routes | 5 | 5 | 0 |
| Blade Views | 9 | 9 | 0 |
| **TOTAL** | **25** | **25** | **0** |

---
*Last updated: 12 Mei 2026 — Semua FASE 1-12 ✅ SELESAI. Siap untuk FASE 13 (Test & Validasi).*
