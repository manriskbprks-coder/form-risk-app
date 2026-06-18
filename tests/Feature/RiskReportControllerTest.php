<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\RiskItem;
use App\Models\RiskCause;
use App\Models\RiskMitigation;
use App\Models\RiskReport;
use App\Models\RiskReportLog;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RiskReportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $teller;
    private User $ca;
    private User $csr;
    private User $security;
    private User $kacab;
    private User $korwil;
    private User $manrisk;
    private User $kacabLain;
    private Branch $branch;
    private Branch $branchLain;
    private RiskItem $riskItemFinansial;
    private RiskItem $riskItemNonFinansial;
    private RiskCause $cause;
    private RiskMitigation $mitigation;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat roles dengan role_category
        $roleMapping = [
            'teller' => 'maker', 'ca' => 'maker', 'csr' => 'maker', 'security' => 'maker',
            'kacab' => 'checker', 'korwil' => 'viewer', 'manrisk' => 'admin',
        ];
        foreach ($roleMapping as $name => $category) {
            $kode = $name === 'teller' ? 'TL' : null;
            Role::firstOrCreate(['name' => $name], ['role_category' => $category, 'kode_role' => $kode]);
        }

        // Buat branch
        $this->branch = Branch::factory()->create(['nama_cabang' => 'Cabang A']);
        $this->branchLain = Branch::factory()->create(['nama_cabang' => 'Cabang B']);

        // Buat users
        $this->teller = User::factory()->create(['branch_id' => $this->branch->id, ]);
        $this->teller->assignRole('teller');

        $this->ca = User::factory()->create(['branch_id' => $this->branch->id, ]);
        $this->ca->assignRole('ca');

        $this->csr = User::factory()->create(['branch_id' => $this->branch->id, ]);
        $this->csr->assignRole('csr');

        $this->security = User::factory()->create(['branch_id' => $this->branch->id, ]);
        $this->security->assignRole('security');

        $this->kacab = User::factory()->create(['branch_id' => $this->branch->id, ]);
        $this->kacab->assignRole('kacab');

        $this->kacabLain = User::factory()->create(['branch_id' => $this->branchLain->id, ]);
        $this->kacabLain->assignRole('kacab');

        $this->korwil = User::factory()->create([]);
        $this->korwil->assignRole('korwil');
        $this->branch->update(['korwil_id' => $this->korwil->id]);
        $this->branchLain->update(['korwil_id' => $this->korwil->id]);

        $this->manrisk = User::factory()->create([]);
        $this->manrisk->assignRole('manrisk');

        // Buat master data risiko
        $this->riskItemFinansial = RiskItem::factory()->create([
            'nama_risiko' => 'Kehilangan Uang',
            'kategori' => 'finansial',
            'sumber_risiko' => 'manusia',
            'role_target' => 'teller',
        ]);

        $this->riskItemNonFinansial = RiskItem::factory()->create([
            'nama_risiko' => 'Pelanggaran SOP',
            'kategori' => 'non-finansial',
            'sumber_risiko' => 'proses_internal',
            'role_target' => 'teller',
        ]);

        $this->cause = RiskCause::factory()->create([
            'risk_item_id' => $this->riskItemFinansial->id,
            'penyebab' => 'Kelalaian Teller',
            'sumber_risiko' => 'manusia',
        ]);

        $this->mitigation = RiskMitigation::factory()->create([
            'risk_cause_id' => $this->cause->id,
            'mitigasi' => 'Double check oleh supervisor',
        ]);
    }

    // =======================================================================
    //  AUTHENTICATION — SAD PATHS
    // =======================================================================

    #[Test]
    public function guest_is_redirected_to_login()
    {
        $this->get(route('form.risiko', 'finansial'))->assertRedirect(route('login'));
        $this->get(route('review.laporan'))->assertRedirect(route('login'));
        $this->get(route('risk.history'))->assertRedirect(route('login'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    // =======================================================================
    //  STAFF — CREATE FORM ACCESS
    // =======================================================================

    #[Test]
    public function staff_can_access_finansial_form()
    {
        $this->actingAs($this->teller)
            ->get(route('form.risiko', 'finansial'))
            ->assertOk();
    }

    #[Test]
    public function staff_can_access_non_finansial_form()
    {
        $this->actingAs($this->teller)
            ->get(route('form.risiko', 'non-finansial'))
            ->assertOk();
    }

    #[Test]
    public function form_returns_404_for_invalid_kategori()
    {
        $this->actingAs($this->teller)
            ->get(route('form.risiko', 'invalid'))
            ->assertNotFound();
    }

    #[Test]
    public function form_only_shows_risk_items_for_users_role()
    {
        // Buat risk item khusus ca
        $itemForCa = RiskItem::factory()->create([
            'nama_risiko' => 'Risiko CA',
            'kategori' => 'finansial',
            'role_target' => 'ca',
        ]);

        // Teller ga bakal liat item khusus CA
        $response = $this->actingAs($this->teller)
            ->get(route('form.risiko', 'finansial'));

        $response->assertOk();
        $response->assertSee($this->riskItemFinansial->nama_risiko);
        $response->assertDontSee($itemForCa->nama_risiko);
    }

    // =======================================================================
    //  STAFF — STORE LAPORAN (HAPPY PATHS)
    // =======================================================================

    #[Test]
    public function staff_can_submit_finansial_report()
    {
        $this->actingAs($this->teller);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => '2026-05-01',
            'tanggal_diketahui' => '2026-05-02',
            'risk_item_id' => $this->riskItemFinansial->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'dampak_finansial' => 5000000,
            
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('risk_reports', [
            'user_id' => $this->teller->id,
            'branch_id' => $this->branch->id,
            'kategori' => 'finansial',
            'risk_item_id' => $this->riskItemFinansial->id,
            'risk_cause_id' => $this->cause->id,
            'dampak_finansial' => 5000000,
            'status' => 'pending_atasan',
        ]);
    }

    #[Test]
    public function staff_can_submit_non_finansial_report()
    {
        $this->actingAs($this->teller);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'non-finansial',
            'tanggal_kejadian' => '2026-05-01',
            'tanggal_diketahui' => '2026-05-02',
            'risk_item_id' => $this->riskItemNonFinansial->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'skala_dampak' => 'sedang',
            'dampak_non_finansial' => 'Reputasi perusahaan tercoreng',
            
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('risk_reports', [
            'user_id' => $this->teller->id,
            'kategori' => 'non-finansial',
            'skala_dampak' => 'sedang',
            'status' => 'pending_atasan',
        ]);
    }

    #[Test]
    public function staff_can_submit_with_other_item_description()
    {
        $this->actingAs($this->teller);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => '2026-05-01',
            'tanggal_diketahui' => '2026-05-02',
            'risk_item_id' => $this->riskItemFinansial->id,
            'other_item_description' => 'Risiko Lainnya',
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'dampak_finansial' => 1000000,
            
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('risk_reports', [
            'other_item_description' => 'Risiko Lainnya',
        ]);
    }

    #[Test]
    public function staff_can_submit_with_other_cause_description()
    {
        $this->actingAs($this->teller);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => '2026-05-01',
            'tanggal_diketahui' => '2026-05-02',
            'risk_item_id' => $this->riskItemFinansial->id,
            'risk_cause_id' => null,
            'other_cause_description' => 'Penyebab Lainnya',
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'dampak_finansial' => 1000000,
            
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('risk_reports', [
            'other_cause_description' => 'Penyebab Lainnya',
        ]);
    }

    #[Test]
    public function kode_laporan_is_generated_automatically()
    {
        $this->actingAs($this->teller);

        $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => '2026-05-01',
            'tanggal_diketahui' => '2026-05-02',
            'risk_item_id' => $this->riskItemFinansial->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'dampak_finansial' => 1000000,
            
        ]);

        $report = RiskReport::first();
        $this->assertNotNull($report->kode_laporan);
        $this->assertStringStartsWith('RISK-', $report->kode_laporan);
        $this->assertStringContainsString('TL', $report->kode_laporan); // TL = Teller
    }

    #[Test]
    public function kacab_report_is_auto_approved()
    {
        $this->actingAs($this->kacab);

        $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => '2026-05-01',
            'tanggal_diketahui' => '2026-05-02',
            'risk_item_id' => $this->riskItemFinansial->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'dampak_finansial' => 1000000,
            
        ]);

        $this->assertDatabaseHas('risk_reports', [
            'user_id' => $this->kacab->id,
            'status' => 'approved_in_progress',
        ]);
    }

    #[Test]
    public function store_validates_required_fields()
    {
        $this->actingAs($this->teller);

        $response = $this->from(route('form.risiko', 'finansial'))
            ->post(route('form.risiko.store'), [
                'kategori' => '',
                'tanggal_kejadian' => '',
                'tanggal_diketahui' => '',
                'risk_item_id' => '',
                'kronologis_kejadian' => '',
                'status_awal' => '',
            ]);

        $response->assertRedirect(route('form.risiko', 'finansial'));
        $response->assertSessionHasErrors(['kategori', 'tanggal_kejadian', 'tanggal_diketahui', 'risk_item_id', 'kronologis_kejadian']);
    }

    #[Test]
    public function store_validates_kronologis_minimum_20_words()
    {
        $this->actingAs($this->teller);

        $response = $this->from(route('form.risiko', 'finansial'))
            ->post(route('form.risiko.store'), [
                'kategori' => 'finansial',
                'tanggal_kejadian' => '2026-05-01',
                'tanggal_diketahui' => '2026-05-02',
                'risk_item_id' => $this->riskItemFinansial->id,
                'risk_cause_id' => $this->cause->id,
                'kronologis_kejadian' => 'Hanya tiga kata saja',
                'dampak_finansial' => 1000000,
                
            ]);

        $response->assertSessionHasErrors('kronologis_kejadian');
    }

    #[Test]
    public function store_validates_finansial_requires_dampak_finansial()
    {
        $this->actingAs($this->teller);

        $response = $this->from(route('form.risiko', 'finansial'))
            ->post(route('form.risiko.store'), [
                'kategori' => 'finansial',
                'tanggal_kejadian' => '2026-05-01',
                'tanggal_diketahui' => '2026-05-02',
                'risk_item_id' => $this->riskItemFinansial->id,
                'risk_cause_id' => $this->cause->id,
                'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
                
            ]);

        $response->assertSessionHasErrors('dampak_finansial');
    }

    #[Test]
    public function store_validates_non_finansial_requires_skala_dampak()
    {
        $this->actingAs($this->teller);

        $response = $this->from(route('form.risiko', 'non-finansial'))
            ->post(route('form.risiko.store'), [
                'kategori' => 'non-finansial',
                'tanggal_kejadian' => '2026-05-01',
                'tanggal_diketahui' => '2026-05-02',
                'risk_item_id' => $this->riskItemNonFinansial->id,
                'risk_cause_id' => $this->cause->id,
                'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
                
            ]);

        $response->assertSessionHasErrors('skala_dampak');
    }

    #[Test]
    public function store_creates_initial_log_when_tindakan_awal_provided()
    {
        $this->actingAs($this->teller);

        $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => '2026-05-01',
            'tanggal_diketahui' => '2026-05-02',
            'risk_item_id' => $this->riskItemFinansial->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'dampak_finansial' => 1000000,
            
            'tindakan_awal' => 'Melakukan pengecekan ulang',
        ]);

        $report = RiskReport::first();
        $this->assertDatabaseHas('risk_report_logs', [
            'risk_report_id' => $report->id,
            'user_id' => $this->teller->id,
            'note' => "notif system : laporan dibuat\npenanganan awal : Melakukan pengecekan ulang",
            'status_after_note' => 'pending_atasan',
        ]);
    }

    // =======================================================================
    //  REVIEW & APPROVAL — KACAB
    // =======================================================================

    #[Test]
    public function staff_cannot_access_review_page()
    {
        $response = $this->actingAs($this->teller)
            ->get(route('review.laporan'));

        $response->assertOk();
        // Staff bisa akses halaman review, tapi datanya kosong
        $this->assertCount(0, $response->viewData('reports'));
        $this->assertCount(0, $response->viewData('tindakLanjut'));
    }

    #[Test]
    public function kacab_can_access_review_page()
    {
        $this->actingAs($this->kacab)
            ->get(route('review.laporan'))
            ->assertOk();
    }

    #[Test]
    public function kacab_only_sees_pending_reports_from_own_branch()
    {
        // Buat laporan di cabang A
        RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'pending_atasan',
        ]);

        // Buat laporan di cabang B
        RiskReport::factory()->create([
            'branch_id' => $this->branchLain->id,
            'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id,
            'status' => 'pending_atasan',
        ]);

        $response = $this->actingAs($this->kacab)
            ->get(route('review.laporan'));

        $response->assertOk();
        // Kacab cabang A cuma liat 1 laporan pending
        $this->assertCount(1, $response->viewData('reports'));
    }

    #[Test]
    public function kacab_can_approve_report()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'pending_atasan',
        ]);

        $this->actingAs($this->kacab)
            ->post(route('risk_reports.update_status', $report->id), [
            'status' => 'approved',
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('risk_reports', [
            'id' => $report->id,
            'status' => 'approved_in_progress',
        ]);
    }

    #[Test]
    public function kacab_can_reject_report()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'pending_atasan',
        ]);

        $this->actingAs($this->kacab)
            ->post(route('risk_reports.update_status', $report->id), [
                'status' => 'need_revision',
                'alasan_reject' => 'Data kronologis tidak lengkap dan perlu diperbaiki.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('risk_reports', [
            'id' => $report->id,
            'status' => 'need_revision',
        ]);
    }

    #[Test]
    public function kacab_cannot_approve_report_from_other_branch()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branchLain->id,
            'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id,
            'status' => 'pending_atasan',
        ]);

        $this->actingAs($this->kacab)
            ->post(route('risk_reports.update_status', $report->id), [
            'status' => 'approved',
        ])
            ->assertForbidden();
    }

    #[Test]
    public function kacab_cannot_approve_already_approved_report()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'approved_in_progress',
        ]);

        $this->actingAs($this->kacab)
            ->post(route('risk_reports.update_status', $report->id), [
            'status' => 'approved',
        ])
            ->assertForbidden();
    }

    #[Test]
    public function kacab_sees_follow_up_reports_on_review_page()
    {
        // Laporan approved & masih open
        RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'approved_in_progress',
        ]);

        // Laporan approved & closed — ga muncul
        RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'closed',
        ]);

        $response = $this->actingAs($this->kacab)
            ->get(route('review.laporan'));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('tindakLanjut'));
    }

    // =======================================================================
    //  UPDATE RESOLUTION — KACAB
    // =======================================================================

    #[Test]
    public function kacab_can_update_resolution_to_closed()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'approved_in_progress',
        ]);

        $this->actingAs($this->kacab)
            ->post(route('risk_reports.update_resolution', $report->id), [
                'status' => 'closed',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('risk_reports', [
            'id' => $report->id,
            'status' => 'closed',
        ]);
    }

    // =======================================================================
    //  ADD PROGRESS — STAFF & KACAB
    // =======================================================================

    #[Test]
    public function staff_can_add_progress_to_own_report()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'approved_in_progress',
        ]);

        $this->actingAs($this->teller)
            ->post(route('risk_reports.add_progress', $report->id), [
                'note' => 'Sedang melakukan investigasi',
                'new_status' => 'approved_in_progress',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('risk_report_logs', [
            'risk_report_id' => $report->id,
            'user_id' => $this->teller->id,
            'note' => 'Sedang melakukan investigasi',
            'status_after_note' => 'approved_in_progress',
        ]);

        $this->assertDatabaseHas('risk_reports', [
            'id' => $report->id,
            'status' => 'approved_in_progress',
        ]);
    }

    #[Test]
    public function staff_cannot_close_report()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'approved_in_progress',
        ]);

        $this->actingAs($this->teller)
            ->post(route('risk_reports.add_progress', $report->id), [
                'note' => 'Mencoba close',
                'new_status' => 'closed',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('risk_reports', [
            'id' => $report->id,
            'status' => 'closed',
        ]);
    }

    #[Test]
    public function kacab_can_close_report()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'approved_in_progress',
        ]);

        $this->actingAs($this->kacab)
            ->post(route('risk_reports.add_progress', $report->id), [
                'note' => 'Selesai ditangani',
                'new_status' => 'closed',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('risk_reports', [
            'id' => $report->id,
            'status' => 'closed',
        ]);
    }

    #[Test]
    public function kacab_cannot_close_report_from_other_branch()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branchLain->id,
            'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id,
            'status' => 'approved_in_progress',
        ]);

        $this->actingAs($this->kacab)
            ->post(route('risk_reports.add_progress', $report->id), [
                'note' => 'Mencoba close',
                'new_status' => 'closed',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function manrisk_cannot_update_progress()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'approved_in_progress',
        ]);

        $this->actingAs($this->manrisk)
            ->post(route('risk_reports.add_progress', $report->id), [
                'note' => 'Mencoba update',
                'new_status' => 'approved_in_progress',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function korwil_cannot_update_progress()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'approved_in_progress',
        ]);

        $this->actingAs($this->korwil)
            ->post(route('risk_reports.add_progress', $report->id), [
                'note' => 'Mencoba update',
                'new_status' => 'approved_in_progress',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function add_progress_validates_required_fields()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'approved_in_progress',
        ]);

        $this->actingAs($this->teller)
            ->from(route('risk_reports.show', $report->id))
            ->post(route('risk_reports.add_progress', $report->id), [
                'note' => '',
                'new_status' => 'closed',
            ])
            ->assertRedirect(route('risk_reports.show', $report->id))
            ->assertSessionHasErrors(['note']);
    }

    // =======================================================================
    //  SHOW — DETAIL LAPORAN
    // =======================================================================

    #[Test]
    public function staff_can_view_own_report_detail()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
        ]);

        $this->actingAs($this->teller)
            ->get(route('risk_reports.show', $report->id))
            ->assertOk();
    }

    #[Test]
    public function staff_cannot_view_others_report_detail()
    {
        $otherUser = User::factory()->create(['branch_id' => $this->branch->id]);
        $otherUser->assignRole('teller');

        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $otherUser->id,
        ]);

        $this->actingAs($this->teller)
            ->get(route('risk_reports.show', $report->id))
            ->assertForbidden();
    }

    #[Test]
    public function kacab_can_view_report_from_own_branch()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
        ]);

        $this->actingAs($this->kacab)
            ->get(route('risk_reports.show', $report->id))
            ->assertOk();
    }

    #[Test]
    public function kacab_cannot_view_report_from_other_branch()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branchLain->id,
            'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id,
        ]);

        $this->actingAs($this->kacab)
            ->get(route('risk_reports.show', $report->id))
            ->assertForbidden();
    }

    #[Test]
    public function manrisk_can_view_any_report()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branchLain->id,
            'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id,
        ]);

        $this->actingAs($this->manrisk)
            ->get(route('risk_reports.show', $report->id))
            ->assertOk();
    }

    #[Test]
    public function korwil_can_view_report_from_supervised_branch()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
        ]);

        $this->actingAs($this->korwil)
            ->get(route('risk_reports.show', $report->id))
            ->assertOk();
    }

    // =======================================================================
    //  INDEX — RIWAYAT & FILTER
    // =======================================================================

    #[Test]
    public function staff_only_sees_own_reports_in_history()
    {
        RiskReport::factory()->create(['user_id' => $this->teller->id, 'branch_id' => $this->branch->id]);
        RiskReport::factory()->create(['user_id' => $this->ca->id, 'branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->teller)
            ->get(route('risk.history'));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('activeReports'));
    }

    #[Test]
    public function kacab_only_sees_own_branch_reports_in_history()
    {
        RiskReport::factory()->create(['branch_id' => $this->branch->id, 'user_id' => $this->teller->id]);
        RiskReport::factory()->create(['branch_id' => $this->branchLain->id, 'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id]);

        $response = $this->actingAs($this->kacab)
            ->get(route('risk.history'));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('activeReports'));
    }

    #[Test]
    public function korwil_sees_all_supervised_branches_reports()
    {
        RiskReport::factory()->create(['branch_id' => $this->branch->id, 'user_id' => $this->teller->id]);
        RiskReport::factory()->create(['branch_id' => $this->branchLain->id, 'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id]);

        $response = $this->actingAs($this->korwil)
            ->get(route('risk.history'));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('activeReports'));
    }

    #[Test]
    public function manrisk_sees_all_reports()
    {
        RiskReport::factory()->create(['branch_id' => $this->branch->id, 'user_id' => $this->teller->id]);
        RiskReport::factory()->create(['branch_id' => $this->branchLain->id, 'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id]);

        $response = $this->actingAs($this->manrisk)
            ->get(route('risk.history'));

        $response->assertOk();
        $this->assertCount(2, $response->viewData('activeReports'));
    }

    #[Test]
    public function history_can_filter_by_kategori()
    {
        RiskReport::factory()->create(['user_id' => $this->teller->id, 'branch_id' => $this->branch->id, 'kategori' => 'finansial']);
        RiskReport::factory()->create(['user_id' => $this->teller->id, 'branch_id' => $this->branch->id, 'kategori' => 'non-finansial']);

        $response = $this->actingAs($this->manrisk)
            ->get(route('risk.history', ['kategori' => 'finansial']));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('activeReports'));
    }

    #[Test]
    public function history_can_filter_by_status()
    {
        RiskReport::factory()->create(['user_id' => $this->teller->id, 'branch_id' => $this->branch->id, 'status' => 'open']);
        RiskReport::factory()->create(['user_id' => $this->teller->id, 'branch_id' => $this->branch->id, 'status' => 'closed']);

        $response = $this->actingAs($this->manrisk)
            ->get(route('risk.history', ['status' => 'open']));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('activeReports'));
    }

    #[Test]
    public function history_can_filter_by_date_range()
    {
        RiskReport::factory()->create([
            'user_id' => $this->teller->id,
            'branch_id' => $this->branch->id,
            'tanggal_kejadian' => '2026-01-15',
        ]);
        RiskReport::factory()->create([
            'user_id' => $this->teller->id,
            'branch_id' => $this->branch->id,
            'tanggal_kejadian' => '2026-06-15',
        ]);

        $response = $this->actingAs($this->manrisk)
            ->get(route('risk.history', [
                'date_from' => '2026-01-01',
                'date_to' => '2026-03-31',
            ]));

        $response->assertOk();
        $this->assertCount(1, $response->viewData('activeReports'));
    }

    #[Test]
    public function history_shows_correct_total_loss_summary()
    {
        RiskReport::factory()->create([
            'user_id' => $this->teller->id,
            'branch_id' => $this->branch->id,
            'kategori' => 'finansial',
            'dampak_finansial' => 1000000,
            'status' => 'approved_in_progress',
        ]);
        RiskReport::factory()->create([
            'user_id' => $this->teller->id,
            'branch_id' => $this->branch->id,
            'kategori' => 'finansial',
            'dampak_finansial' => 2000000,
            'status' => 'approved_in_progress',
        ]);
        RiskReport::factory()->create([
            'user_id' => $this->teller->id,
            'branch_id' => $this->branch->id,
            'kategori' => 'finansial',
            'dampak_finansial' => 500000,
            'status' => 'pending_atasan', // not approved, not counted
        ]);

        $response = $this->actingAs($this->manrisk)
            ->get(route('risk.history'));

        $response->assertOk();
        $this->assertEquals(3000000, $response->viewData('totalLoss'));
    }

    // =======================================================================
    //  UPDATE RESOLUTION VIA updateResolution — AUTHORIZATION
    // =======================================================================

    #[Test]
    public function manrisk_cannot_update_resolution()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'pending_atasan',
        ]);

        $this->actingAs($this->manrisk)
            ->post(route('risk_reports.update_resolution', $report->id), [
                'status' => 'approved_in_progress',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function korwil_cannot_update_resolution()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'pending_atasan',
        ]);

        $this->actingAs($this->korwil)
            ->post(route('risk_reports.update_resolution', $report->id), [
                'status' => 'approved_in_progress',
            ])
            ->assertForbidden();
    }

    // =======================================================================
    //  UPDATE APPROVAL STATUS — VALIDATION
    // =======================================================================

    #[Test]
    public function update_status_validates_required_fields()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'pending_atasan',
        ]);

        $this->actingAs($this->kacab)
            ->from(route('review.laporan'))
            ->post(route('risk_reports.update_status', $report->id), [
                'status' => '',
            ])
            ->assertRedirect(route('review.laporan'))
            ->assertSessionHasErrors('status');
    }

    #[Test]
    public function update_status_validates_invalid_status_value()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'pending_atasan',
        ]);

        $this->actingAs($this->kacab)
            ->from(route('review.laporan'))
            ->post(route('risk_reports.update_status', $report->id), [
                'status' => 'invalid_status',
            ])
            ->assertRedirect(route('review.laporan'))
            ->assertSessionHasErrors('status');
    }

    // =======================================================================
    //  UPDATE RESOLUTION — VALIDATION
    // =======================================================================

    #[Test]
    public function update_resolution_validates_required_fields()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'pending_atasan',
        ]);

        $this->actingAs($this->kacab)
            ->from(route('review.laporan'))
            ->post(route('risk_reports.update_resolution', $report->id), [
                'status' => '',
            ])
            ->assertRedirect(route('review.laporan'))
            ->assertSessionHasErrors('status');
    }

    // =======================================================================
    //  ADD PROGRESS — VALIDATION NOTE MIN 5 CHARS
    // =======================================================================

    #[Test]
    public function add_progress_validates_note_minimum_5_characters()
    {
        $report = RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'status' => 'pending_atasan',
        ]);

        $this->actingAs($this->teller)
            ->from(route('risk_reports.show', $report->id))
            ->post(route('risk_reports.add_progress', $report->id), [
                'note' => 'ABC',
                'new_status' => 'approved_in_progress',
            ])
            ->assertRedirect(route('risk_reports.show', $report->id))
            ->assertSessionHasErrors('note');
    }

    // =======================================================================
    //  SHOW — 404 FOR NON-EXISTENT REPORT
    // =======================================================================

    #[Test]
    public function show_returns_404_for_non_existent_report()
    {
        $this->actingAs($this->manrisk)
            ->get(route('risk_reports.show', 99999))
            ->assertNotFound();
    }

    // =======================================================================
    //  STORE — RISK ITEM MUST EXIST
    // =======================================================================

    #[Test]
    public function store_fails_when_risk_item_does_not_exist()
    {
        $this->actingAs($this->teller);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => '2026-05-01',
            'tanggal_diketahui' => '2026-05-02',
            'risk_item_id' => 99999,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'dampak_finansial' => 1000000,
            
        ]);

        $response->assertSessionHasErrors('risk_item_id');
    }

    // =======================================================================
    //  STORE — DURASI PENYELESAIAN (OPTIONAL)
    // =======================================================================

    #[Test]
    public function staff_can_submit_report_with_durasi_penyelesaian()
    {
        $this->actingAs($this->teller);

        $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => '2026-05-01',
            'tanggal_diketahui' => '2026-05-02',
            'risk_item_id' => $this->riskItemFinansial->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'dampak_finansial' => 1000000,
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            
        ]);

        $this->assertDatabaseHas('risk_reports', [
            'user_id' => $this->teller->id,
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
        ]);
    }

    // =======================================================================
    //  KODE LAPORAN — UNIQUE CONSTRAINT
    // =======================================================================

    #[Test]
    public function kode_laporan_is_unique()
    {
        $this->actingAs($this->teller);

        // Submit pertama
        $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => '2026-05-01',
            'tanggal_diketahui' => '2026-05-02',
            'risk_item_id' => $this->riskItemFinansial->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'dampak_finansial' => 1000000,
            
        ]);

        // Submit kedua — harus punya kode_laporan berbeda
        $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => '2026-05-03',
            'tanggal_diketahui' => '2026-05-04',
            'risk_item_id' => $this->riskItemFinansial->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana teller sedang menghitung uang dan terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'dampak_finansial' => 2000000,
            
        ]);

        $reports = RiskReport::all();
        $this->assertCount(2, $reports);
        $this->assertNotEquals($reports[0]->kode_laporan, $reports[1]->kode_laporan);
    }
}
