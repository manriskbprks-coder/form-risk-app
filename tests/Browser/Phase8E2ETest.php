<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Branch;
use App\Models\RiskReport;
use App\Models\RiskItem;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Phase8E2ETest extends DuskTestCase
{
    protected static $tellerUser;
    protected static $kacabUser;
    protected static $manriskUser;
    protected static $korwilUser;
    protected static $branchA;
    protected static $branchB;
    protected static $dataReady = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!static::$dataReady) {
            static::$branchA = Branch::firstOrCreate(
                ['nama_cabang' => 'Cabang A E2E'],
                ['kode_cabang' => 'E2E-A', 'status' => 'active']
            );
            static::$branchB = Branch::firstOrCreate(
                ['nama_cabang' => 'Cabang B E2E'],
                ['kode_cabang' => 'E2E-B', 'status' => 'active']
            );

            static::$tellerUser = User::firstOrCreate(
                ['username' => 'teller_e2e'],
                ['name' => 'Teller E2E', 'password' => bcrypt('password'), 'branch_id' => static::$branchA->id, 'password_changed_at' => now()]
            );
            if (!static::$tellerUser->hasRole('teller')) static::$tellerUser->assignRole('teller');

            static::$kacabUser = User::firstOrCreate(
                ['username' => 'kacab_e2e'],
                ['name' => 'Kacab E2E', 'password' => bcrypt('password'), 'branch_id' => static::$branchA->id, 'password_changed_at' => now()]
            );
            if (!static::$kacabUser->hasRole('kacab')) static::$kacabUser->assignRole('kacab');

            static::$manriskUser = User::firstOrCreate(
                ['username' => 'manrisk_e2e'],
                ['name' => 'ManRisk E2E', 'password' => bcrypt('password'), 'branch_id' => static::$branchA->id, 'password_changed_at' => now()]
            );
            if (!static::$manriskUser->hasRole('manrisk')) static::$manriskUser->assignRole('manrisk');

            static::$korwilUser = User::firstOrCreate(
                ['username' => 'korwil_e2e'],
                ['name' => 'Korwil E2E', 'password' => bcrypt('password'), 'branch_id' => static::$branchA->id, 'password_changed_at' => now()]
            );
            if (!static::$korwilUser->hasRole('korwil')) static::$korwilUser->assignRole('korwil');

            static::$dataReady = true;
        }
    }

    // ================================================================
    // TEST 1: LOGIN SUCCESS
    // ================================================================
    public function test_login_success_redirects_to_dashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->waitFor('input[name="username"]', 10)
                    ->type('input[name="username"]', 'teller_e2e')
                    ->type('input[name="password"]', 'password')
                    ->press('button[type="submit"]')
                    ->waitForLocation('/dashboard', 15)
                    ->assertPathIs('/dashboard')
                    ->assertSee('Selamat Datang kembali')
                    ->assertSee('Teller E2E')
                    ->screenshot('login-success');
        });
    }

    // ================================================================
    // TEST 2: LOGIN FAILED
    // ================================================================
    public function test_login_failed_shows_error_modal()
    {
        $this->browse(function (Browser $browser) {
            // Logout dulu biar ga session conflict
            $browser->logout()
                    ->visit('/login')
                    ->waitFor('input[name="username"]', 10)
                    ->type('input[name="username"]', 'teller_e2e')
                    ->type('input[name="password"]', 'wrongpassword')
                    ->press('button[type="submit"]')
                    ->waitForText('Login Gagal', 10)
                    ->assertSee('Login Gagal')
                    ->assertSee('Password yang Anda masukkan salah')
                    ->screenshot('login-failed');
        });
    }

    // ================================================================
    // TEST 3: DASHBOARD TELLER
    // ================================================================
    public function test_dashboard_shows_stat_cards_for_teller()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$tellerUser)
                    ->visit('/dashboard')
                    ->waitForText('Menunggu Review', 10)
                    ->assertSee('Menunggu Review')
                    ->assertSee('Perlu Ditindak Lanjut')
                    ->assertSee('Selesai')
                    ->assertSee('Form Pelaporan Risiko')
                    ->screenshot('dashboard-teller');
        });
    }

    // ================================================================
    // TEST 4: DASHBOARD KACAB
    // ================================================================
    public function test_dashboard_shows_stat_cards_for_kacab()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$kacabUser)
                    ->visit('/dashboard')
                    ->waitForText('Nilai Dampak', 10)
                    ->assertSee('Nilai Dampak')
                    ->assertSee('Review & Tindak Lanjut')
                    ->assertSee('Deklarasi Nihil Risiko')
                    ->screenshot('dashboard-kacab');
        });
    }

    // ================================================================
    // TEST 5: DASHBOARD MANRISK
    // ================================================================
    public function test_dashboard_shows_ringkasan_wilayah_for_manrisk()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$manriskUser)
                    ->visit('/dashboard')
                    ->waitForText('Ringkasan Wilayah', 10)
                    ->assertSee('Ringkasan Wilayah')
                    ->assertSee('Laporan Terbaru')
                    ->screenshot('dashboard-manrisk');
        });
    }

    // ================================================================
    // TEST 6: FORM RISIKO FINANSIAL
    // ================================================================
    public function test_form_risiko_finansial_shows_form_elements()
    {
        $this->browse(function (Browser $browser) {
            $riskItem = RiskItem::where('kategori', 'finansial')->first();

            $browser->loginAs(static::$tellerUser)
                    ->visit('/form-risiko/finansial')
                    ->waitForText('Form Input Risiko Operasional (Maker)', 10)
                    ->assertSee('Identitas Kejadian')
                    ->assertSee('Detail Risiko')
                    ->assertSee('Dampak Finansial')
                    ->assertSee('Submit Laporan')
                    ->screenshot('form-finansial');
        });
    }

    // ================================================================
    // TEST 7: FORM RISIKO NON-FINANSIAL
    // ================================================================
    public function test_form_risiko_non_finansial_shows_skala_dampak()
    {
        $this->browse(function (Browser $browser) {
            $riskItem = RiskItem::where('kategori', 'non-finansial')->first();

            $browser->loginAs(static::$tellerUser)
                    ->visit('/form-risiko/non-finansial')
                    ->waitForText('Analisa Dampak Non-Finansial', 10)
                    ->assertSee('Sangat Tinggi')
                    ->assertSee('Tinggi')
                    ->assertSee('Sedang')
                    ->assertSee('Rendah')
                    ->assertSee('Sangat Rendah')
                    ->screenshot('form-non-finansial');
        });
    }

    // ================================================================
    // TEST 8: KACAB REVIEW & APPROVE
    // ================================================================
    public function test_kacab_can_see_review_page_and_approve_report()
    {
        $this->browse(function (Browser $browser) {
            $report = RiskReport::factory()->create([
                'branch_id' => static::$branchA->id,
                'user_id' => static::$tellerUser->id,
                'approval_status' => 'pending_kacab',
                'kategori' => 'finansial',
                'dampak_finansial' => 500000,
            ]);

            $browser->loginAs(static::$kacabUser)
                    ->visit('/review-laporan')
                    ->waitForText('Menunggu Persetujuan Anda', 10)
                    ->assertSee($report->kode_laporan)
                    ->assertSee('Approve')
                    ->assertSee('Reject')
                    ->screenshot('review-page');

            $browser->press('Approve')
                    ->waitForText('Menunggu Tindak Lanjut', 10)
                    ->screenshot('review-approved');
        });
    }

    // ================================================================
    // TEST 9: KACAB REJECT
    // ================================================================
    public function test_kacab_can_reject_report_with_reason()
    {
        $this->browse(function (Browser $browser) {
            $report = RiskReport::factory()->create([
                'branch_id' => static::$branchA->id,
                'user_id' => static::$tellerUser->id,
                'approval_status' => 'pending_kacab',
                'kategori' => 'finansial',
                'dampak_finansial' => 250000,
            ]);

            $browser->loginAs(static::$kacabUser)
                    ->visit('/review-laporan')
                    ->waitForText($report->kode_laporan, 10)
                    ->tap(function ($b) {
                        $b->script('document.querySelector("button[onclick*=\'Reject\']")?.click()');
                    })
                    ->waitFor('#rejectModal:not(.hidden)', 5)
                    ->type('alasan_reject', 'Data kurang lengkap, mohon dilengkapi dengan bukti pendukung.')
                    ->press('Kirim Penolakan')
                    ->waitForText('Menunggu Persetujuan Anda', 10)
                    ->screenshot('review-rejected');
        });
    }

    // ================================================================
    // TEST 10: HISTORY PAGE
    // ================================================================
    public function test_history_page_shows_reports_and_filter()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$tellerUser)
                    ->visit('/riwayat-risiko')
                    ->waitForText('Riwayat & Monitoring Risiko Operasional', 10)
                    ->assertSee('Riwayat & Monitoring')
                    ->screenshot('history-page');
        });
    }

    // ================================================================
    // TEST 11: DETAIL REPORT
    // ================================================================
    public function test_detail_report_shows_complete_information()
    {
        $this->browse(function (Browser $browser) {
            $report = RiskReport::factory()->create([
                'branch_id' => static::$branchA->id,
                'user_id' => static::$tellerUser->id,
                'approval_status' => 'approved',
                'kategori' => 'finansial',
                'dampak_finansial' => 1000000,
                'kronologis_kejadian' => 'Nasabah melakukan transfer melalui mobile banking namun dana tidak masuk karena sistem error.',
            ]);

            $browser->loginAs(static::$tellerUser)
                    ->visit('/risk-report/' . $report->id)
                    ->waitForText('Detail Laporan Risiko', 10)
                    ->assertSee($report->kode_laporan)
                    ->screenshot('detail-report');
        });
    }

    // ================================================================
    // TEST 12: DEKLARASI NIHIL
    // ================================================================
    public function test_kacab_can_access_deklarasi_nihil()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$kacabUser)
                    ->visit('/deklarasi-nihil')
                    ->waitForText('Deklarasi Nihil Risiko', 10)
                    ->assertSee('Checklist Jabatan')
                    ->assertSee('Pernyataan Tanggung Jawab')
                    ->screenshot('deklarasi-nihil');
        });
    }

    // ================================================================
    // TEST 13: RISK MASTER
    // ================================================================
    public function test_manrisk_can_access_risk_master()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$manriskUser)
                    ->visit('/admin/risk-master')
                    ->waitForText('Control Center', 10)
                    ->assertSee('Tambah Pertanyaan Risiko Baru')
                    ->screenshot('risk-master');
        });
    }

    // ================================================================
    // TEST 14: 404 PAGE
    // ================================================================
    public function test_404_page_shows_custom_error()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$tellerUser)
                    ->visit('/this-page-does-not-exist-12345')
                    ->waitForText('404', 10)
                    ->screenshot('error-404');
        });
    }

    // ================================================================
    // TEST 15: 403 PAGE
    // ================================================================
    public function test_403_page_shows_for_unauthorized_access()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$tellerUser)
                    ->visit('/admin/users')
                    ->waitForText('403', 10)
                    ->screenshot('error-403');
        });
    }

    // ================================================================
    // TEST 16: SIDEBAR TELLER
    // ================================================================
    public function test_sidebar_navigation_works_for_teller()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$tellerUser)
                    ->visit('/dashboard')
                    ->waitForText('Riwayat Saya', 10)
                    ->clickLink('Riwayat Saya')
                    ->waitForLocation('/riwayat-risiko', 10)
                    ->assertPathIs('/riwayat-risiko')
                    ->screenshot('sidebar-riwayat');
        });
    }

    // ================================================================
    // TEST 17: SIDEBAR KACAB
    // ================================================================
    public function test_sidebar_navigation_works_for_kacab()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$kacabUser)
                    ->visit('/dashboard')
                    ->waitForText('Review & Tindak Lanjut', 10)
                    ->clickLink('Review & Tindak Lanjut')
                    ->waitForLocation('/review-laporan', 10)
                    ->assertPathIs('/review-laporan')
                    ->screenshot('sidebar-review');
        });
    }

    // ================================================================
    // TEST 18: PROFILE PAGE
    // ================================================================
    public function test_profile_page_can_be_accessed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$tellerUser)
                    ->visit('/profile')
                    ->waitForText('Profile', 10)
                    ->assertSee('Ganti Password')
                    ->screenshot('profile-page');
        });
    }

    // ================================================================
    // TEST 19: NOTIFICATIONS
    // ================================================================
    public function test_notifications_page_shows_list()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$tellerUser)
                    ->visit('/notifications')
                    ->waitForText('Notifikasi', 10)
                    ->screenshot('notifications');
        });
    }

    // ================================================================
    // TEST 20: EXPORT CSV
    // ================================================================
    public function test_export_csv_downloads_file()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$tellerUser)
                    ->visit('/export-risiko')
                    ->pause(3000)
                    ->screenshot('export-csv');
        });
    }

    // ================================================================
    // TEST 21: DEKLARASI NIHIL HISTORY
    // ================================================================
    public function test_deklarasi_nihil_history_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$kacabUser)
                    ->visit('/deklarasi-nihil/riwayat')
                    ->waitForText('Riwayat Deklarasi', 10)
                    ->screenshot('deklarasi-nihil-history');
        });
    }

    // ================================================================
    // TEST 22: DASHBOARD KORWIL
    // ================================================================
    public function test_dashboard_shows_for_korwil()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$korwilUser)
                    ->visit('/dashboard')
                    ->waitForText('Nilai Dampak', 10)
                    ->screenshot('dashboard-korwil');
        });
    }

    // ================================================================
    // TEST 23: FORM RISIKO SUBMIT (E2E)
    // ================================================================
    public function test_form_risiko_submit_creates_report()
    {
        $this->browse(function (Browser $browser) {
            $riskItem = RiskItem::where('kategori', 'finansial')->first();
            $riskCause = $riskItem ? $riskItem->causes()->first() : null;

            $browser->visit('/logout')
                    ->loginAs(static::$tellerUser)
                    ->visit('/form-risiko/finansial')
                    ->waitForText('Form Input Risiko Operasional (Maker)', 10)
                    ->type('tanggal_kejadian', now()->format('Y-m-d'))
                    ->type('tanggal_diketahui', now()->format('Y-m-d'))
                    ->select('risk_item_id', $riskItem->id)
                    ->pause(2000)
                    ->select('risk_cause_id', $riskCause->id)
                    ->type('kronologis_kejadian', 'Nasabah mengalami kehilangan buku tabungan dan melaporkan ke petugas teller untuk dilakukan pemblokiran rekening dan penerbitan buku tabungan baru. Kejadian ini terjadi pada saat jam operasional dan langsung ditangani oleh petugas yang bertugas.')
                    ->type('dampak_finansial', 500000)
                    ->select('status_awal', 'open')
                    ->tap(function ($b) {
                        $b->script('window.scrollTo(0, document.body.scrollHeight);');
                        $b->script('document.querySelector("button[type=\'submit\']").click();');
                    })
                    ->pause(5000)
                    ->screenshot('form-submit-result');
        });
    }

    // ================================================================
    // TEST 24: TINDAK LANJUT (UPDATE RESOLUTION)
    // ================================================================
    public function test_kacab_can_update_resolution()
    {
        $this->browse(function (Browser $browser) {
            $report = RiskReport::factory()->create([
                'branch_id' => static::$branchA->id,
                'user_id' => static::$tellerUser->id,
                'approval_status' => 'approved',
                'kategori' => 'finansial',
                'dampak_finansial' => 300000,
                'resolution_status' => 'in_progress',
            ]);

            $browser->loginAs(static::$kacabUser)
                    ->visit('/risk-report/' . $report->id)
                    ->waitForText('Detail Laporan Risiko', 10)
                    ->screenshot('tindak-lanjut');
        });
    }

    // ================================================================
    // TEST 25: PROGRESS CATATAN
    // ================================================================
    public function test_add_progress_note()
    {
        $this->browse(function (Browser $browser) {
            $report = RiskReport::factory()->create([
                'branch_id' => static::$branchA->id,
                'user_id' => static::$tellerUser->id,
                'approval_status' => 'approved',
                'kategori' => 'finansial',
                'dampak_finansial' => 200000,
            ]);

            $browser->loginAs(static::$kacabUser)
                    ->visit('/risk-report/' . $report->id)
                    ->waitForText('Detail Laporan Risiko', 10)
                    ->screenshot('progress-note');
        });
    }

    // ================================================================
    // TEST 26: REVISI LAPORAN
    // ================================================================
    public function test_request_revision_flow()
    {
        $this->browse(function (Browser $browser) {
            $report = RiskReport::factory()->create([
                'branch_id' => static::$branchA->id,
                'user_id' => static::$tellerUser->id,
                'approval_status' => 'pending_kacab',
                'kategori' => 'finansial',
                'dampak_finansial' => 150000,
            ]);

            $browser->loginAs(static::$kacabUser)
                    ->visit('/risk-report/' . $report->id)
                    ->waitForText('Detail Laporan Risiko', 10)
                    ->screenshot('revision-page');
        });
    }

    // ================================================================
    // TEST 27: NOTIFICATIONS MARK ALL READ
    // ================================================================
    public function test_notifications_mark_all_read()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$tellerUser)
                    ->visit('/notifications')
                    ->waitForText('Notifikasi', 10)
                    ->screenshot('notifications-mark-read');
        });
    }

    // ================================================================
    // TEST 28: BRANCH MANAGEMENT (MANRISK)
    // ================================================================
    public function test_manrisk_can_access_branch_management()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$manriskUser)
                    ->visit('/branches-management')
                    ->waitForText('Manajemen Cabang', 10)
                    ->screenshot('branch-management');
        });
    }

    // ================================================================
    // TEST 29: ADMIN USERS PAGE (MANRISK)
    // ================================================================
    public function test_manrisk_can_access_admin_users()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$manriskUser)
                    ->visit('/admin/users')
                    ->waitForText('Manajemen Pengguna', 10)
                    ->screenshot('admin-users');
        });
    }

    // ================================================================
    // TEST 30: LOGOUT
    // ================================================================
    public function test_user_can_logout()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(static::$tellerUser)
                    ->visit('/dashboard')
                    ->waitForText('Selamat Datang kembali', 10)
                    ->tap(function ($b) {
                        $b->script('document.querySelector("form[action*=\'logout\'] button[type=\'submit\']")?.click()');
                    })
                    ->waitForLocation('/login', 15)
                    ->assertPathIs('/login')
                    ->screenshot('logout-success');
        });
    }

    // ================================================================
    // TEST 31: DASHBOARD CSR
    // ================================================================
    public function test_dashboard_shows_for_csr()
    {
        $this->browse(function (Browser $browser) {
            $csrUser = User::firstOrCreate(
                ['username' => 'csr_e2e'],
                ['name' => 'CSR E2E', 'email' => 'csr_e2e@test.com', 'password' => bcrypt('password'), 'branch_id' => static::$branchA->id, 'password_changed_at' => now()]
            );
            if (!$csrUser->hasRole('csr')) $csrUser->assignRole('csr');

            $browser->loginAs($csrUser)
                    ->visit('/dashboard')
                    ->waitForText('Menunggu Review', 10)
                    ->screenshot('dashboard-csr');
        });
    }

    // ================================================================
    // TEST 32: DASHBOARD SECURITY
    // ================================================================
    public function test_dashboard_shows_for_security()
    {
        $this->browse(function (Browser $browser) {
            $securityUser = User::firstOrCreate(
                ['username' => 'security_e2e'],
                ['name' => 'Security E2E', 'email' => 'security_e2e@test.com', 'password' => bcrypt('password'), 'branch_id' => static::$branchA->id, 'password_changed_at' => now()]
            );
            if (!$securityUser->hasRole('security')) $securityUser->assignRole('security');

            $browser->loginAs($securityUser)
                    ->visit('/dashboard')
                    ->waitForText('Menunggu Review', 10)
                    ->screenshot('dashboard-security');
        });
    }

    // ================================================================
    // TEST 33: DASHBOARD CA
    // ================================================================
    public function test_dashboard_shows_for_ca()
    {
        $this->browse(function (Browser $browser) {
            $caUser = User::firstOrCreate(
                ['username' => 'ca_e2e'],
                ['name' => 'CA E2E', 'email' => 'ca_e2e@test.com', 'password' => bcrypt('password'), 'branch_id' => static::$branchA->id, 'password_changed_at' => now()]
            );
            if (!$caUser->hasRole('ca')) $caUser->assignRole('ca');

            $browser->loginAs($caUser)
                    ->visit('/dashboard')
                    ->waitForText('Menunggu Review', 10)
                    ->screenshot('dashboard-ca');
        });
    }
}
