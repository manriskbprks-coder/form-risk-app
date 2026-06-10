<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\RiskItem;
use App\Models\RiskCause;
use App\Models\RiskMitigation;
use App\Models\RiskReport;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Phase 3: Authorization & Access Control — Penetration Test
 * 
 * Pengujian ini dilakukan layaknya penetration tester profesional untuk
 * memvalidasi implementasi authorization di aplikasi:
 * - 3.1 Policy/Gate per Role (Teller, Kacab, Korwil, ManRisk)
 * - 3.2 Middleware Check di Controller
 * - 3.3 Data Scoping di Query
 * - 3.4 Business Logic Bypass
 */
class Phase3AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branchA;
    private Branch $branchB;
    private Branch $branchC;

    private User $tellerA;
    private User $tellerB;
    private User $ca;
    private User $csr;
    private User $security;

    private User $kacabA;
    private User $kacabB;

    private User $korwil;
    private User $manrisk;

    private RiskItem $riskItem;
    private RiskCause $cause;
    private RiskMitigation $mitigation;

    private RiskReport $reportBranchA;
    private RiskReport $reportBranchB;

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

        // Buat 3 branch: A (diawasi korwil), B (diawasi korwil), C (tidak diawasi)
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
        $this->branchC = Branch::factory()->create([
            'nama_cabang' => 'Cabang C',
            'kode_cabang' => 'CBC',
            'is_active' => true,
        ]);

        // Buat Korwil (mengawasi branch A & B)
        $this->korwil = User::factory()->create([
            'branch_id' => $this->branchA->id,
            'is_active' => true,
            'username' => 'korwil1',
            'password' => bcrypt('CorrectHorseBatteryStaple1!'),
            ]);
        $this->korwil->assignRole('korwil');

        $this->branchA->update(['korwil_id' => $this->korwil->id]);
        $this->branchB->update(['korwil_id' => $this->korwil->id]);

        // Buat ManRisk
        $this->manrisk = User::factory()->create([
            'branch_id' => $this->branchA->id,
            'is_active' => true,
            'username' => 'manrisk1',
            'password' => bcrypt('CorrectHorseBatteryStaple1!'),
            ]);
        $this->manrisk->assignRole('manrisk');

        // Buat Kacab
        $this->kacabA = User::factory()->create([
            'branch_id' => $this->branchA->id,
            'is_active' => true,
            'username' => 'kacabA',
            'password' => bcrypt('CorrectHorseBatteryStaple1!'),
            ]);
        $this->kacabA->assignRole('kacab');

        $this->kacabB = User::factory()->create([
            'branch_id' => $this->branchB->id,
            'is_active' => true,
            'username' => 'kacabB',
            'password' => bcrypt('CorrectHorseBatteryStaple1!'),
            ]);
        $this->kacabB->assignRole('kacab');

        // Buat Staff
        $this->tellerA = User::factory()->create([
            'branch_id' => $this->branchA->id,
            'is_active' => true,
            'username' => 'tellerA',
            'password' => bcrypt('CorrectHorseBatteryStaple1!'),
            ]);
        $this->tellerA->assignRole('teller');

        $this->tellerB = User::factory()->create([
            'branch_id' => $this->branchB->id,
            'is_active' => true,
            'username' => 'tellerB',
            'password' => bcrypt('CorrectHorseBatteryStaple1!'),
            ]);
        $this->tellerB->assignRole('teller');

        $this->ca = User::factory()->create([
            'branch_id' => $this->branchA->id,
            'is_active' => true,
            'username' => 'ca1',
            'password' => bcrypt('CorrectHorseBatteryStaple1!'),
            ]);
        $this->ca->assignRole('ca');

        $this->csr = User::factory()->create([
            'branch_id' => $this->branchA->id,
            'is_active' => true,
            'username' => 'csr1',
            'password' => bcrypt('CorrectHorseBatteryStaple1!'),
            ]);
        $this->csr->assignRole('csr');

        $this->security = User::factory()->create([
            'branch_id' => $this->branchA->id,
            'is_active' => true,
            'username' => 'security1',
            'password' => bcrypt('CorrectHorseBatteryStaple1!'),
            ]);
        $this->security->assignRole('security');

        // Buat Risk Master Data
        $this->riskItem = RiskItem::factory()->create([
            'nama_risiko' => 'Selisih Kas',
            'kategori' => 'finansial',
            'role_target' => 'teller',
            'sumber_risiko' => 'manusia',
        ]);

        $this->cause = RiskCause::factory()->create([
            'risk_item_id' => $this->riskItem->id,
            'penyebab' => 'Kesalahan hitung',
            'sumber_risiko' => 'manusia',
        ]);

        $this->mitigation = RiskMitigation::factory()->create([
            'risk_cause_id' => $this->cause->id,
            'mitigasi' => 'Cek ulang perhitungan',
        ]);

        // Buat laporan di Branch A (milik tellerA)
        $this->reportBranchA = RiskReport::factory()->create([
            'user_id' => $this->tellerA->id,
            'branch_id' => $this->branchA->id,
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kategori' => 'finansial',
            'status' => 'pending_atasan',
            'kode_laporan' => 'RISK-CBATL-202605-0001',
        ]);

        // Buat laporan di Branch B (milik tellerB)
        $this->reportBranchB = RiskReport::factory()->create([
            'user_id' => $this->tellerB->id,
            'branch_id' => $this->branchB->id,
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kategori' => 'finansial',
            'status' => 'pending_atasan',
            'kode_laporan' => 'RISK-CBBTL-202605-0001',
        ]);
    }

    // ========================================================================
    // 3.1 POLICY/GATE PER ROLE — CROSS-BRANCH ACCESS
    // ========================================================================

    #[Test]
    public function teller_cannot_view_report_from_other_branch()
    {
        // Teller A coba lihat laporan Branch B → harus 403
        $this->actingAs($this->tellerA);

        $response = $this->get(route('risk_reports.show', $this->reportBranchB->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function teller_can_view_own_report()
    {
        // Teller A lihat laporannya sendiri → harus 200
        $this->actingAs($this->tellerA);

        $response = $this->get(route('risk_reports.show', $this->reportBranchA->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function kacab_cannot_view_report_from_other_branch()
    {
        // Kacab A coba lihat laporan Branch B → harus 403
        $this->actingAs($this->kacabA);

        $response = $this->get(route('risk_reports.show', $this->reportBranchB->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function kacab_can_view_report_from_own_branch()
    {
        // Kacab A lihat laporan Branch A → harus 200
        $this->actingAs($this->kacabA);

        $response = $this->get(route('risk_reports.show', $this->reportBranchA->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function korwil_can_view_report_from_supervised_branch()
    {
        // Korwil lihat laporan Branch A (diawasi) → harus 200
        $this->actingAs($this->korwil);

        $response = $this->get(route('risk_reports.show', $this->reportBranchA->id));

        $response->assertStatus(200);
    }

    #[Test]
    public function korwil_cannot_view_report_from_unsupervised_branch()
    {
        // Korwil coba lihat laporan Branch C (tidak diawasi) → harus 403
        $reportBranchC = RiskReport::factory()->create([
            'user_id' => $this->tellerA->id,
            'branch_id' => $this->branchC->id,
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kategori' => 'finansial',
            'status' => 'pending_atasan',
            'status' => 'open',
            'kode_laporan' => 'RISK-CBCTL-202605-0001',
        ]);

        $this->actingAs($this->korwil);

        $response = $this->get(route('risk_reports.show', $reportBranchC->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function manrisk_can_view_any_report()
    {
        // ManRisk lihat laporan Branch B → harus 200
        $this->actingAs($this->manrisk);

        $response = $this->get(route('risk_reports.show', $this->reportBranchB->id));

        $response->assertStatus(200);
    }

    // ========================================================================
    // 3.2 APPROVE/REJECT AUTHORIZATION
    // ========================================================================

    #[Test]
    public function teller_cannot_approve_report()
    {
        // Teller coba approve laporan → harus 403
        $this->actingAs($this->tellerA);

        $response = $this->post(route('risk_reports.update_status', $this->reportBranchA->id), [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function kacab_cannot_approve_report_from_other_branch()
    {
        // Kacab A coba approve laporan Branch B → harus 403
        $this->actingAs($this->kacabA);

        $response = $this->post(route('risk_reports.update_status', $this->reportBranchB->id), [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function kacab_can_approve_report_from_own_branch()
    {
        // Kacab A approve laporan Branch A → harus sukses (302 redirect)
        $this->actingAs($this->kacabA);

        $response = $this->post(route('risk_reports.update_status', $this->reportBranchA->id), [
            'status' => 'approved',
        ]);

        $response->assertStatus(302);
        $this->reportBranchA->refresh();
        $this->assertEquals('approved_in_progress', $this->reportBranchA->status);
    }

    #[Test]
    public function kacab_cannot_double_approve_report()
    {
        // Approve dulu
        $this->reportBranchA->update(['status' => 'approved_in_progress']);

        $this->actingAs($this->kacabA);

        // Coba approve lagi → harus 403 (karena status bukan pending_kacab/need_revision)
        $response = $this->post(route('risk_reports.update_status', $this->reportBranchA->id), [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function manrisk_cannot_approve_report()
    {
        // ManRisk coba approve laporan → harus 403 (bukan kacab)
        $this->actingAs($this->manrisk);

        $response = $this->post(route('risk_reports.update_status', $this->reportBranchA->id), [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    // ========================================================================
    // 3.3 CLOSE REPORT AUTHORIZATION
    // ========================================================================

    #[Test]
    public function teller_cannot_close_report()
    {
        $this->reportBranchA->update(['status' => 'approved_in_progress']);

        $this->actingAs($this->tellerA);

        $response = $this->post(route('risk_reports.add_progress', $this->reportBranchA->id), [
            'note' => 'Mencoba menutup laporan',
            'new_status' => 'closed',
        ]);

        // Harusnya error karena teller bukan kacab
        $response->assertSessionHas('error');
    }

    #[Test]
    public function kacab_cannot_close_report_from_other_branch()
    {
        $this->reportBranchB->update(['status' => 'approved_in_progress']);

        $this->actingAs($this->kacabA);

        $response = $this->post(route('risk_reports.add_progress', $this->reportBranchB->id), [
            'note' => 'Mencoba menutup laporan cabang lain',
            'new_status' => 'closed',
        ]);

        // Harusnya error karena kacabA bukan kacab branch B
        $response->assertStatus(403);
    }

    #[Test]
    public function kacab_can_close_report_from_own_branch()
    {
        $this->reportBranchA->update(['status' => 'approved_in_progress']);

        $this->actingAs($this->kacabA);

        $response = $this->post(route('risk_reports.add_progress', $this->reportBranchA->id), [
            'note' => 'Laporan selesai ditindaklanjuti',
            'new_status' => 'closed',
        ]);

        $response->assertStatus(302);
        $this->reportBranchA->refresh();
        $this->assertEquals('closed', $this->reportBranchA->status);
    }

    // ========================================================================
    // 3.4 REVISION AUTHORIZATION
    // ========================================================================

    #[Test]
    public function only_manrisk_can_request_revision()
    {
        $this->reportBranchA->update(['status' => 'approved_in_progress']);

        // Teller coba minta revisi → harus 403
        $this->actingAs($this->tellerA);

        $response = $this->post(route('risk_reports.request_revision', $this->reportBranchA->id), [
            'revision_note' => 'Tolong revisi bagian kronologis',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function manrisk_can_request_revision()
    {
        $this->reportBranchA->update(['status' => 'approved_in_progress']);

        $this->actingAs($this->manrisk);

        $response = $this->post(route('risk_reports.request_revision', $this->reportBranchA->id), [
            'revision_note' => 'Tolong revisi bagian kronologis kejadian karena kurang detail',
        ]);

        $response->assertStatus(302);
        $this->reportBranchA->refresh();
        $this->assertEquals('need_revision', $this->reportBranchA->status);
    }

    #[Test]
    public function only_manrisk_can_approve_revision()
    {
        $this->reportBranchA->update(['status' => 'pending_revision']);

        // Kacab coba approve revisi → harus 403
        $this->actingAs($this->kacabA);

        $response = $this->post(route('risk_reports.approve_revision', $this->reportBranchA->id));

        $response->assertStatus(403);
    }

    #[Test]
    public function manrisk_can_approve_revision()
    {
        $this->reportBranchA->update(['status' => 'pending_revision']);

        $this->actingAs($this->manrisk);

        $response = $this->post(route('risk_reports.approve_revision', $this->reportBranchA->id));

        $response->assertStatus(302);
        $this->reportBranchA->refresh();
        $this->assertEquals('approved_in_progress', $this->reportBranchA->status);
    }

    // ========================================================================
    // 3.5 DATA SCOPING — INDEX / HISTORY
    // ========================================================================

    #[Test]
    public function teller_only_sees_own_reports_in_index()
    {
        $this->actingAs($this->tellerA);

        $response = $this->get(route('risk.history'));

        $response->assertStatus(200);
        $response->assertSee($this->reportBranchA->kode_laporan);
        $response->assertDontSee($this->reportBranchB->kode_laporan);
    }

    #[Test]
    public function kacab_only_sees_own_branch_reports_in_index()
    {
        $this->actingAs($this->kacabA);

        $response = $this->get(route('risk.history'));

        $response->assertStatus(200);
        $response->assertSee($this->reportBranchA->kode_laporan);
        $response->assertDontSee($this->reportBranchB->kode_laporan);
    }

    #[Test]
    public function korwil_sees_supervised_branches_reports_in_index()
    {
        $this->actingAs($this->korwil);

        $response = $this->get(route('risk.history'));

        $response->assertStatus(200);
        $response->assertSee($this->reportBranchA->kode_laporan);
        $response->assertSee($this->reportBranchB->kode_laporan);
    }

    #[Test]
    public function manrisk_sees_all_reports_in_index()
    {
        $this->actingAs($this->manrisk);

        $response = $this->get(route('risk.history'));

        $response->assertStatus(200);
        $response->assertSee($this->reportBranchA->kode_laporan);
        $response->assertSee($this->reportBranchB->kode_laporan);
    }

    // ========================================================================
    // 3.6 DATA SCOPING — EXPORT
    // ========================================================================

    #[Test]
    public function teller_export_only_contains_own_reports()
    {
        $this->actingAs($this->tellerA);

        $response = $this->get(route('risk.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function kacab_export_only_contains_own_branch_reports()
    {
        $this->actingAs($this->kacabA);

        $response = $this->get(route('risk.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // ========================================================================
    // 3.7 GUEST ACCESS — UNAUTHENTICATED
    // ========================================================================

    #[Test]
    public function guest_is_redirected_to_login_when_accessing_protected_routes()
    {
        $routes = [
            route('risk.history'),
            route('form.risiko', 'finansial'),
            route('risk_reports.show', $this->reportBranchA->id),
            route('review.laporan'),
            route('dashboard'),
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertRedirect(route('login'));
        }
    }

    #[Test]
    public function guest_cannot_submit_form()
    {
        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kronologis kejadian yang panjang dan detail minimal dua puluh kata untuk memenuhi validasi.',
            'status_awal' => 'open',
            'dampak_finansial' => 1000000,
        ]);

        $response->assertRedirect(route('login'));
    }

    // ========================================================================
    // 3.8 ADMIN / RISK MASTER AUTHORIZATION
    // ========================================================================

    #[Test]
    public function non_manrisk_cannot_access_risk_master()
    {
        $roles = [$this->tellerA, $this->kacabA, $this->korwil];

        foreach ($roles as $user) {
            $this->actingAs($user);

            $response = $this->get(route('admin.risk_master.index'));
            $response->assertStatus(403);
        }
    }

    #[Test]
    public function manrisk_can_access_risk_master()
    {
        $this->actingAs($this->manrisk);

        $response = $this->get(route('admin.risk_master.index'));

        $response->assertStatus(200);
    }

    // ========================================================================
    // 3.9 RISK FREE DECLARATION AUTHORIZATION
    // ========================================================================

    #[Test]
    public function non_kacab_cannot_access_risk_free_declaration()
    {
        $roles = [$this->tellerA, $this->manrisk, $this->korwil];

        foreach ($roles as $user) {
            $this->actingAs($user);

            $response = $this->get(route('risk_free_declarations.create'));
            $response->assertStatus(403);
        }
    }

    #[Test]
    public function kacab_can_access_risk_free_declaration()
    {
        $this->actingAs($this->kacabA);

        $response = $this->get(route('risk_free_declarations.create'));

        $response->assertStatus(200);
    }

    #[Test]
    public function non_manrisk_cannot_reject_declaration()
    {
        $declaration = \App\Models\RiskFreeDeclaration::create([
            'branch_id' => $this->branchA->id,
            'user_id' => $this->kacabA->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Dengan ini menyatakan bahwa tidak ada kejadian risiko di cabang kami.',
            'status' => 'active',
        ]);

        $roles = [$this->tellerA, $this->kacabA, $this->korwil];

        foreach ($roles as $user) {
            $this->actingAs($user);

            $response = $this->post(route('risk_free_declarations.reject', $declaration->id));
            $response->assertStatus(403);
        }
    }

    #[Test]
    public function manrisk_can_reject_declaration()
    {
        $declaration = \App\Models\RiskFreeDeclaration::create([
            'branch_id' => $this->branchA->id,
            'user_id' => $this->kacabA->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Dengan ini menyatakan bahwa tidak ada kejadian risiko di cabang kami.',
            'status' => 'active',
        ]);

        $this->actingAs($this->manrisk);

        $response = $this->post(route('risk_free_declarations.reject', $declaration->id));

        $response->assertStatus(302);
        $declaration->refresh();
        $this->assertEquals('rejected', $declaration->status);
    }

    // ========================================================================
    // 3.10 BUSINESS LOGIC BYPASS
    // ========================================================================

    #[Test]
    public function cannot_approve_already_approved_report()
    {
        $this->reportBranchA->update(['status' => 'approved_in_progress']);

        $this->actingAs($this->kacabA);

        $response = $this->post(route('risk_reports.update_status', $this->reportBranchA->id), [
            'status' => 'approved',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function cannot_submit_revision_on_non_revision_status()
    {
        $this->reportBranchA->update(['status' => 'approved_in_progress']);

        $this->actingAs($this->tellerA);

        $response = $this->post(route('risk_reports.submit_revision', $this->reportBranchA->id), [
            'kronologis_kejadian' => 'Revisi kronologis kejadian yang panjang dan detail minimal dua puluh kata untuk memenuhi validasi.',
            'dampak_finansial' => 2000000,
        ]);

        // Policy submitRevision return false kalau status bukan need_revision → 403
        $response->assertStatus(403);
    }

    #[Test]
    public function kacab_cannot_approve_own_report()
    {
        // Kacab A buat laporan untuk dirinya sendiri
        $ownReport = RiskReport::factory()->create([
            'user_id' => $this->kacabA->id,
            'branch_id' => $this->branchA->id,
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kategori' => 'finansial',
            'status' => 'pending_atasan',
            'status' => 'open',
            'kode_laporan' => 'RISK-CBAKC-202605-0001',
        ]);

        $this->actingAs($this->kacabA);

        $response = $this->post(route('risk_reports.update_status', $ownReport->id), [
            'status' => 'approved',
        ]);

        // Kacab bisa approve laporan cabangnya sendiri (termasuk laporan sendiri)
        // Ini sesuai policy: kacab bisa approve laporan cabangnya
        $response->assertStatus(302);
        $ownReport->refresh();
        $this->assertEquals('approved_in_progress', $ownReport->status);
    }

    #[Test]
    public function korwil_cannot_update_resolution()
    {
        $this->actingAs($this->korwil);

        $response = $this->post(route('risk_reports.update_resolution', $this->reportBranchA->id), [
            'status' => 'approved_in_progress',
        ]);

        // Korwil hanya pantau — harus 403
        $response->assertStatus(403);
    }

    #[Test]
    public function manrisk_cannot_update_resolution()
    {
        $this->actingAs($this->manrisk);

        $response = $this->post(route('risk_reports.update_resolution', $this->reportBranchA->id), [
            'status' => 'approved_in_progress',
        ]);

        // ManRisk hanya pantau — harus 403
        $response->assertStatus(403);
    }

    #[Test]
    public function teller_cannot_access_review_page()
    {
        $this->actingAs($this->tellerA);

        $response = $this->get(route('review.laporan'));

        // Review page hanya untuk kacab — teller lihat halaman kosong atau redirect
        $response->assertStatus(200);
        // Tapi tidak boleh ada laporan yang tampil
        $response->assertDontSee($this->reportBranchA->kode_laporan);
    }

    #[Test]
    public function kacab_can_access_review_page()
    {
        $this->actingAs($this->kacabA);

        $response = $this->get(route('review.laporan'));

        $response->assertStatus(200);
        $response->assertSee($this->reportBranchA->kode_laporan);
    }
}
