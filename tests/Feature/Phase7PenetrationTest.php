<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\RiskItem;
use App\Models\RiskCause;
use App\Models\RiskMitigation;
use App\Models\RiskReport;
use App\Models\RiskFreeDeclaration;
use App\Models\RiskFreeDeclarationDetail;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Phase 7: Penetration Test — Advanced Security Testing
 * 
 * Pengujian ini melengkapi skenario penetration testing yang belum tercakup
 * di Phase 1-6, meliputi:
 * - 7.1 IDOR (Insecure Direct Object Reference)
 * - 7.2 Mass Assignment
 * - 7.3 Timing Attack / Rate Limiting
 * - 7.4 Business Logic Bypass (lanjutan)
 */
class Phase7PenetrationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branchA;
    private Branch $branchB;

    private User $tellerA;
    private User $tellerB;
    private User $kacabA;
    private User $kacabB;
    private User $manrisk;
    private User $staffBiasa;

    private RiskItem $riskItem;
    private RiskCause $cause;
    private RiskMitigation $mitigation;

    private const KRONOLOGIS_20_KATA = 'Testing kronologis untuk penetration testing phase tujuh ini harus mencapai minimal dua puluh kata agar validasi dapat berjalan dengan baik dan benar sekali.';

    protected function setUp(): void
    {
        parent::setUp();

        // Buat roles dengan role_category
        $roleMapping = [
            'teller' => 'maker', 'ca' => 'maker', 'csr' => 'maker', 'security' => 'maker',
            'kacab' => 'checker', 'korwil' => 'viewer', 'manrisk' => 'admin',
        ];
        foreach ($roleMapping as $name => $category) {
            Role::firstOrCreate(['name' => $name], ['role_category' => $category]);
        }

        // Buat 2 cabang berbeda
        $this->branchA = Branch::factory()->create([
            'nama_cabang' => 'Cabang A',
            'kode_cabang' => 'CBA',
            'is_active' => true,
        ]);

        $this->branchB = Branch::factory()->create([
            'nama_cabang' => 'Cabang B',
            'kode_cabang' => 'CBB',
            'is_active' => true,
        ]);

        // Buat user di cabang A
        $this->tellerA = User::factory()->create([
            'branch_id' => $this->branchA->id,
        ]);
        $this->tellerA->assignRole('teller');

        $this->kacabA = User::factory()->create([
            'branch_id' => $this->branchA->id,
        ]);
        $this->kacabA->assignRole('kacab');

        // Buat user di cabang B
        $this->tellerB = User::factory()->create([
            'branch_id' => $this->branchB->id,
        ]);
        $this->tellerB->assignRole('teller');

        $this->kacabB = User::factory()->create([
            'branch_id' => $this->branchB->id,
        ]);
        $this->kacabB->assignRole('kacab');

        // ManRisk — bisa akses semua
        $this->manrisk = User::factory()->create([
            'branch_id' => $this->branchA->id,
        ]);
        $this->manrisk->assignRole('manrisk');

        // Staff biasa tanpa role khusus
        $this->staffBiasa = User::factory()->create();
        // Tidak assign role apapun

        // Buat master data risiko
        $this->riskItem = RiskItem::factory()->create([
            'kategori' => 'finansial',
            'role_target' => 'teller',
            'sumber_risiko' => 'manusia',
        ]);

        $this->cause = RiskCause::factory()->create([
            'risk_item_id' => $this->riskItem->id,
            'sumber_risiko' => 'manusia',
        ]);

        $this->mitigation = RiskMitigation::factory()->create([
            'risk_cause_id' => $this->cause->id,
        ]);
    }

    // ========================================================================
    // 7.1 IDOR (Insecure Direct Object Reference)
    // ========================================================================

    #[Test]
    public function teller_A_tidak_bisa_melihat_laporan_teller_B()
    {
        // Teller B buat laporan
        $reportB = $this->createReportByUser($this->tellerB);

        // Teller A coba lihat laporan milik Teller B
        $this->actingAs($this->tellerA);
        $response = $this->get(route('risk_reports.show', $reportB->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function kacab_A_tidak_bisa_approve_laporan_cabang_B()
    {
        // Teller B buat laporan di cabang B
        $reportB = $this->createReportByUser($this->tellerB);

        // Kacab A coba approve laporan cabang B
        $this->actingAs($this->kacabA);
        $response = $this->post(route('risk_reports.update_status', $reportB->id), [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function staff_biasa_tidak_bisa_mengakses_halaman_admin_risk_master()
    {
        $this->actingAs($this->staffBiasa);
        $response = $this->get(route('admin.risk_master.index'));

        // Route admin pakai middleware 'role:manrisk' — harus 403
        $response->assertStatus(403);
    }

    #[Test]
    public function staff_biasa_tidak_bisa_reject_deklarasi_cabang_lain()
    {
        // Kacab B buat deklarasi
        $this->actingAs($this->kacabB);
        $this->post(route('risk_free_declarations.store'), [
            'statement_text' => 'Saya menyatakan bahwa tidak ada laporan risiko pada periode ini.',
            'jabatan' => [
                'Teller' => ['is_clean' => true, 'keterangan' => null],
                'CA' => ['is_clean' => true, 'keterangan' => null],
            ],
        ]);

        $declaration = RiskFreeDeclaration::where('branch_id', $this->branchB->id)->first();

        // Staff biasa coba reject
        $this->actingAs($this->staffBiasa);
        $response = $this->post(route('risk_free_declarations.reject', $declaration->id));

        $response->assertStatus(403);
    }

    // ========================================================================
    // 7.2 MASS ASSIGNMENT
    // ========================================================================

    #[Test]
    public function mass_assignment_field_is_admin_diabaikan()
    {
        $this->actingAs($this->tellerA);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            'status_awal' => 'open',
            // Mass assignment attempt
            'is_admin' => 1,
            'role' => 'superadmin',
        ]);

        $response->assertSessionHas('success');

        // Pastikan user tetap teller, bukan superadmin
        $this->assertFalse($this->tellerA->fresh()->hasRole('superadmin'));
        $this->assertTrue($this->tellerA->fresh()->hasRole('teller'));
    }

    #[Test]
    public function mass_assignment_user_id_tidak_bisa_diubah()
    {
        $this->actingAs($this->tellerA);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            'status_awal' => 'open',
            // Mass assignment attempt — coba jadi user lain
            'user_id' => $this->tellerB->id,
        ]);

        $response->assertSessionHas('success');

        $report = RiskReport::where('kode_laporan', 'LIKE', "RISK-CBATL-%")->first();
        $this->assertNotNull($report);
        // user_id harus milik tellerA, bukan tellerB
        $this->assertEquals($this->tellerA->id, $report->user_id);
        $this->assertNotEquals($this->tellerB->id, $report->user_id);
    }

    #[Test]
    public function mass_assignment_branch_id_tidak_bisa_diubah()
    {
        $this->actingAs($this->tellerA);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            'status_awal' => 'open',
            // Mass assignment attempt — coba jadi cabang lain
            'branch_id' => $this->branchB->id,
        ]);

        $response->assertSessionHas('success');

        $report = RiskReport::where('kode_laporan', 'LIKE', "RISK-CBATL-%")->first();
        $this->assertNotNull($report);
        // branch_id harus milik branchA (cabang tellerA), bukan branchB
        $this->assertEquals($this->branchA->id, $report->branch_id);
        $this->assertNotEquals($this->branchB->id, $report->branch_id);
    }

    // ========================================================================
    // 7.3 TIMING ATTACK / RATE LIMITING
    // ========================================================================

    #[Test]
    public function brute_force_login_kena_throttle_setelah_5_percobaan()
    {
        // 5 percobaan login gagal — harusnya masih bisa
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login'), [
                'username' => $this->tellerA->username,
                'password' => 'wrong-password-' . $i,
            ]);
            // Harusnya redirect back dengan error (bukan throttle)
            // LoginRequest pake key 'email' untuk error message
            $response->assertSessionHasErrors('email');
        }

        // Percobaan ke-6 — harus kena throttle 429
        $response = $this->post(route('login'), [
            'username' => $this->tellerA->username,
            'password' => 'wrong-password-6',
        ]);

        $response->assertStatus(429);
    }

    #[Test]
    public function store_report_kena_throttle_setelah_10_percobaan()
    {
        $this->actingAs($this->tellerA);

        // 10 percobaan store — harusnya masih bisa
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post(route('form.risiko.store'), [
                'kategori' => 'finansial',
                'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
                'tanggal_diketahui' => now()->format('Y-m-d'),
                'risk_item_id' => $this->riskItem->id,
                'risk_cause_id' => $this->cause->id,
                'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
                'dampak_finansial' => 5000000,
                'skala_dampak' => 'sedang',
                'durasi_penyelesaian' => 3,
                'durasi_satuan' => 'hari',
                'status_awal' => 'open',
            ]);
        }

        // Percobaan ke-11 — harus kena throttle 429
        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            'status_awal' => 'open',
        ]);

        $response->assertStatus(429);
    }

    // ========================================================================
    // 7.4 BUSINESS LOGIC BYPASS (Lanjutan)
    // ========================================================================

    #[Test]
    public function kacab_A_tidak_bisa_close_laporan_cabang_B()
    {
        // Teller B buat laporan, Kacab B approve
        $reportB = $this->createReportByUser($this->tellerB);
        $this->actingAs($this->kacabB);
        $this->post(route('risk_reports.update_status', $reportB->id), [
            'status' => 'approved',
        ]);

        // Kacab A coba close laporan cabang B
        $this->actingAs($this->kacabA);
        $response = $this->post(route('risk_reports.add_progress', $reportB->id), [
            'note' => 'Mencoba menutup laporan cabang lain.',
            'new_status' => 'closed',
        ]);

        // Policy updateProgress cek view() dulu — Kacab A branch_id != report branch_id
        // Jadi Gate authorize return 403
        $response->assertStatus(403);
    }

    #[Test]
    public function double_approve_laporan_yang_sudah_approved_ditolak()
    {
        // Teller A buat laporan
        $report = $this->createReportByUser($this->tellerA);

        // Kacab A approve
        $this->actingAs($this->kacabA);
        $response = $this->post(route('risk_reports.update_status', $report->id), [
            'status' => 'approved',
        ]);
        $response->assertSessionHas('success');

        // Kacab A coba approve lagi — harus ditolak karena status sudah 'approved'
        $response2 = $this->post(route('risk_reports.update_status', $report->id), [
            'status' => 'approved',
        ]);

        // Policy approve hanya untuk pending_kacab atau need_revision
        $response2->assertStatus(403);
    }

    #[Test]
    public function reject_deklarasi_yang_sudah_direject_ditolak()
    {
        // Kacab A buat deklarasi
        $this->actingAs($this->kacabA);
        $this->post(route('risk_free_declarations.store'), [
            'statement_text' => 'Saya menyatakan bahwa tidak ada laporan risiko pada periode ini.',
            'jabatan' => [
                'Teller' => ['is_clean' => true, 'keterangan' => null],
                'CA' => ['is_clean' => true, 'keterangan' => null],
            ],
        ]);

        $declaration = RiskFreeDeclaration::where('branch_id', $this->branchA->id)->first();

        // ManRisk reject pertama kali
        $this->actingAs($this->manrisk);
        $response = $this->post(route('risk_free_declarations.reject', $declaration->id));
        $response->assertSessionHas('success');

        // ManRisk coba reject lagi — harus ditolak karena status sudah 'rejected'
        $response2 = $this->post(route('risk_free_declarations.reject', $declaration->id));
        $response2->assertSessionHas('error');
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    private function createReportByUser(User $user): RiskReport
    {
        $this->actingAs($user);

        $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            'status_awal' => 'open',
        ]);

        return RiskReport::where('user_id', $user->id)->latest()->first();
    }
}
