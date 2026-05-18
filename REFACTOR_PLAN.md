# REFACTOR PLAN: Hapus Hardcoded Role Names → Role Category

## 🎯 Tujuan
Ganti semua referensi hardcoded role names (manrisk, kacab, teller, ca, csr, security, korwil) dengan role_category-based logic. Biar kalo user nambah role baru (misal "kabag_akunting" dgn category maker), semuanya langsung jalan.

## 🧠 Konsep Mapping
| Role Name Asli | Role Category Baru |
|----------------|-------------------|
| manrisk        | admin             |
| korwil         | viewer            |
| kacab          | checker           |
| teller, ca, csr, security | maker |

---

## 📋 FASE-FASE (Kerjain Per Fase)

### FASE 1: Migration role_target di risk_items
**File: `database/migrations/YYYY_MM_DD_HHMMSS_change_role_target_in_risk_items.php`** (file baru)

**Apa yang diubah:**
- `role_target` itu VARCHAR biasa (ga pake ENUM lagi)
- Update data: teller/ca/csr/security → maker, kacab → checker, korwil → viewer

**Cara:**
```php
// Schema:
Schema::table('risk_items', function (Blueprint $table) {
    $table->string('role_target', 50)->change();
});

// Data update:
DB::table('risk_items')
    ->whereIn('role_target', ['teller', 'ca', 'csr', 'security'])
    ->update(['role_target' => 'maker']);

DB::table('risk_items')
    ->where('role_target', 'kacab')
    ->update(['role_target' => 'checker']);

DB::table('risk_items')
    ->where('role_target', 'korwil')
    ->update(['role_target' => 'viewer']);
```

---

### FASE 2: Update RiskMasterSeeder
**File: `database/seeders/RiskMasterSeeder.php`**

**Apa yang diubah:**
- Semua `'role_target' => 'teller'` → `'role_target' => 'maker'`
- Semua `'role_target' => 'ca'` → `'role_target' => 'maker'`
- Semua `'role_target' => 'csr'` → `'role_target' => 'maker'`
- Semua `'role_target' => 'security'` → `'role_target' => 'maker'`
- Semua `'role_target' => 'kacab'` → `'role_target' => 'checker'`

**Total perubahan:** ~35 entries di array `$masterData`

---

### FASE 3: RiskReportController@create
**File: `app/Http/Controllers/RiskReportController.php`**

**Yang diubah:**
- **Line 33-36**: Variable `$userRole = $this->primaryRoleName()` masih dipake buat ngecek role null → bisa dihapus
- **Line 43-46**: Filter `role_target`:
  ```php
  // BEFORE:
  ->where('role_target', $userRole)
  
  // AFTER:
  ->where('role_target', Auth::user()->roleCategory())
  ```
- **Line 57-65**: `$roleMap` di `generateKodeLaporan()` → **TETAP** pake `primaryRoleName()` karena ini kode laporan (TL/CA/CS/KC), bukan role_category

---

### FASE 4: RiskReportPolicy
**File: `app/Policies/RiskReportPolicy.php`**

**Yang diubah:**
- **Line 95**: `$user->hasRole('manrisk')` → `$user->isAdmin()`
- **Line 122**: `$user->hasRole('manrisk')` → `$user->isAdmin()`

---

### FASE 5: RiskFreeDeclarationController
**File: `app/Http/Controllers/RiskFreeDeclarationController.php`**

**Yang diubah:**
- **Line 147**: `User::role('manrisk')->get()` → query by role_category='admin'
- **Line 166**: `$user->hasRole('manrisk')` → `$user->isAdmin()`
- **Line 194**: `User::role('kacab')->where('branch_id', ...)` → query by role_category='checker'

---

### FASE 6: RiskReportController Notifikasi
**File: `app/Http/Controllers/RiskReportController.php`**

**Yang diubah:**
- **Line 126**: `User::role('kacab')->where('branch_id', ...)` → `User::whereHas('roles', fn($q) => $q->where('role_category', 'checker'))->where('branch_id', ...)`
- **Line 528**: Sama, `User::role('kacab')` → query by role_category
- **Line 540**: `User::role('manrisk')->get()` → query by role_category='admin'

---

### FASE 7: Routes web.php
**File: `routes/web.php`**

**Yang diubah:**
- **Line 21**: `$role = $user?->primaryRoleName()` — still needed for some things
- **Line 31-42**: Branch filter logic:
  - `if ($role === 'korwil')` → `if ($roleCategory === 'viewer')`
- **Line 71-74**: Report query:
  - `if ($role === 'korwil')` → `if ($roleCategory === 'viewer')`
- **Line 283**: `if ($role === 'manrisk')` → `if ($roleCategory === 'admin')`
- **Line 493**: `Route::middleware(['auth', 'role:manrisk'])` → pake closure middleware:
  ```php
  Route::middleware(['auth', function ($request, $next) {
      if (!$request->user()->isAdmin()) {
          abort(403, 'Akses ditolak. Hanya admin.');
      }
      return $next($request);
  }])->group(function () { ... });
  ```

---

### FASE 8: Layout app.blade.php
**File: `resources/views/layouts/app.blade.php`**

**Yang diubah:**
- **Line 62-72**: `@hasrole('manrisk')` untuk icon Dashboard → `@if(Auth::user()->isAdmin())`
- **Line 159-194**: `@hasrole('manrisk')` untuk section Administrasi → `@if(Auth::user()->isAdmin())`

---

### FASE 9: Dashboard blade
**File: `resources/views/dashboard.blade.php`**

**Yang diubah:**
- **Line 53**: `@hasanyrole('teller|ca|csr|security')` → `@if(Auth::user()->roleCategory() === 'maker')`
- **Line 136**: `@hasrole('manrisk')` (filter dropdown) → `@if(Auth::user()->isAdmin())`
- **Line 178**: `@hasanyrole('kacab|korwil|manrisk')` (charts) → `@if(Auth::user()->isChecker() || Auth::user()->isViewer() || Auth::user()->isAdmin())`
- **Line 222**: `@hasanyrole('teller|ca|csr|security|kacab')` (form cards) → `@if(Auth::user()->canCreateReport())`
- **Line 254**: `@hasrole('kacab')` (review section) → `@if(Auth::user()->isChecker())`
- **Line 281**: `@hasrole('kacab')` (deklarasi) → `@if(Auth::user()->isChecker())`
- **Line 341**: `@hasrole('manrisk')` (ringkasan wilayah) → `@if(Auth::user()->isAdmin())`

---

### FASE 10: RoleController
**File: `app/Http/Controllers/Admin/RoleController.php`**

**Yang diubah:**
- **Line 42-44**: Hapus blok proteksi role bawaan di `update()`
- **Line 65-67**: Hapus blok proteksi role bawaan di `destroy()`
- Tetep retain: `$role->users()->count() > 0` check

---

### FASE 11: Roles Index Blade
**File: `resources/views/admin/roles/index.blade.php`**

**Yang diubah:**
- **Line 48**: Hapus `$isBawaan = in_array(...)` variable
- **Line 52**: Hapus `$isBawaan` check untuk row styling
- **Line 56-61**: Hapus logic "BAWAAN"/"KUSTOM" badge
- **Line 81-86**: Hapus "Sistem"/"Kustom" badge
- **Line 88-97**: Semua role tampilkan Edit & Hapus (hapus `@if(!$isBawaan)`)

---

### FASE 12: ExportRiskReportController
**File: `app/Http/Controllers/ExportRiskReportController.php`**

**Yang diubah:**
- **Line 29**: `$user->hasRole('korwil')` → `$user->roleCategory() === 'viewer' && $user->supervisedBranches()->exists()`

---

### FASE 13: Test & Validasi
**Commands:**
```bash
php artisan migrate:fresh --seed
php artisan test
```

Pastikan 272+ tests passing.

---

## 📊 Progress Tracker

- [x] **FASE 1**: Migration role_target
- [x] **FASE 2**: Update RiskMasterSeeder
- [x] **FASE 3**: RiskReportController@create
- [x] **FASE 4**: RiskReportPolicy
- [x] **FASE 5**: RiskFreeDeclarationController
- [x] **FASE 6**: RiskReportController notifikasi
- [x] **FASE 7**: Routes web.php
- [x] **FASE 8**: Layout app.blade.php
- [x] **FASE 9**: Dashboard blade
- [x] **FASE 10**: RoleController
- [x] **FASE 11**: Roles Index Blade
- [x] **FASE 12**: ExportRiskReportController
- [ ] **FASE 13**: Test & Validasi (menyusul)

---

## ⚠️ Yang Tetap Pake Role Name (Gak Diubah)
| Lokasi | Alasan |
|--------|--------|
| `generateKodeLaporan()` roleMap (TL/CA/CS/KC) | Kode laporan berdasarkan jabatan, bukan kategori |
| `primaryRoleName()` method di User model | Masih dipake di view buat nampilin jabatan user |
| Nama file blade, route names | Ga related |
