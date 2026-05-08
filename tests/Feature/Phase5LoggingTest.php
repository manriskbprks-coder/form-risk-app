<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\RiskReport;
use App\Models\RiskReportLog;
use App\Models\RiskItem;
use App\Models\RiskCause;
use App\Models\RiskMitigation;
use App\Models\RiskFreeDeclaration;
use App\Models\RiskFreeDeclarationDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use PHPUnit\Framework\Attributes\Test;

class Phase5LoggingTest extends TestCase
{
    use RefreshDatabase;

    private User $manrisk;
    private User $kacab;
    private User $teller;
    private Branch $branch;
    private RiskItem $riskItem;
    private RiskCause $riskCause;
    private RiskMitigation $mitigation;

    private const KRONOLOGIS_20_KATA = 'Testing kronologis untuk logging phase ini harus mencapai minimal dua puluh kata agar validasi dapat berjalan dengan baik dan benar sekali.';

    protected function setUp(): void
    {
        parent::setUp();

        // Buat roles
        collect(['teller', 'ca', 'csr', 'security', 'kacab', 'korwil', 'manrisk'])
            ->each(fn ($r) => Role::firstOrCreate(['name' => $r]));

        // Buat branch
        $this->branch = Branch::factory()->create([
            'nama_cabang' => 'Cabang Test Logging',
            'kode_cabang' => 'TST',
        ]);

        // Buat user dengan role
        $this->manrisk = User::factory()->create([
            'branch_id' => $this->branch->id,
        ]);
        $this->manrisk->assignRole('manrisk');

        $this->kacab = User::factory()->create([
            'branch_id' => $this->branch->id,
        ]);
        $this->kacab->assignRole('kacab');

        $this->teller = User::factory()->create([
            'branch_id' => $this->branch->id,
        ]);
        $this->teller->assignRole('teller');

        // Buat master data risiko
        $this->riskItem = RiskItem::factory()->create([
            'kategori' => 'finansial',
            'role_target' => 'teller',
            'sumber_risiko' => 'manusia',
        ]);

        $this->riskCause = RiskCause::factory()->create([
            'risk_item_id' => $this->riskItem->id,
            'sumber_risiko' => 'manusia',
        ]);

        $this->mitigation = RiskMitigation::factory()->create([
            'risk_cause_id' => $this->riskCause->id,
        ]);
    }

    // ========================================================================
    // 5.1 AUDIT TRAIL — RiskReportLog
    // ========================================================================

    #[Test]
    public function log_menyimpan_old_data_snapshot_saat_create()
    {
        $this->actingAs($this->teller);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->riskCause->id,
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            'status_awal' => 'open',
        ]);

        $response->assertSessionHas('success');

        $report = RiskReport::where('user_id', $this->teller->id)->first();
        $this->assertNotNull($report);

        $log = RiskReportLog::where('risk_report_id', $report->id)
            ->where('note', 'Laporan dibuat')
            ->first();

        $this->assertNotNull($log, 'Log "Laporan dibuat" harus tercatat');
        $this->assertNotNull($log->old_data, 'old_data harus terisi saat create');
        
        $oldData = json_decode($log->old_data, true);
        $this->assertIsArray($oldData, 'old_data harus berupa JSON valid');
        $this->assertArrayHasKey('kronologis_kejadian', $oldData);
        $this->assertArrayHasKey('dampak_finansial', $oldData);
        $this->assertArrayHasKey('skala_dampak', $oldData);
    }

    #[Test]
    public function log_menyimpan_user_id_yang_benar()
    {
        $this->actingAs($this->teller);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->riskCause->id,
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            'status_awal' => 'open',
        ]);

        $report = RiskReport::where('user_id', $this->teller->id)->first();
        $log = RiskReportLog::where('risk_report_id', $report->id)->first();

        $this->assertEquals($this->teller->id, $log->user_id, 'User_id log harus sesuai dengan pembuat aksi');
    }

    #[Test]
    public function log_approval_menyimpan_user_id_kacab()
    {
        // Teller buat laporan
        $report = $this->createReportByTeller();

        // Kacab approve
        $this->actingAs($this->kacab);
        $response = $this->post(route('risk_reports.update_status', $report->id), [
            'status' => 'approved',
        ]);

        $response->assertSessionHas('success');

        $log = RiskReportLog::where('risk_report_id', $report->id)
            ->where('status_after_note', 'approved')
            ->first();

        $this->assertNotNull($log, 'Log approval harus tercatat');
        $this->assertEquals($this->kacab->id, $log->user_id, 'User_id log approval harus Kacab');
    }

    #[Test]
    public function log_reject_menyimpan_catatan_revisi()
    {
        $report = $this->createReportByTeller();

        $this->actingAs($this->kacab);
        $response = $this->post(route('risk_reports.update_status', $report->id), [
            'status' => 'rejected',
            'alasan_reject' => 'Data kronologis kurang lengkap, mohon dilengkapi.',
        ]);

        $response->assertSessionHas('success');

        $log = RiskReportLog::where('risk_report_id', $report->id)
            ->where('status_after_note', 'need_revision')
            ->first();

        $this->assertNotNull($log, 'Log reject/revisi harus tercatat');
        $this->assertStringContainsString('Revisi diminta oleh Kacab', $log->note);
        $this->assertStringContainsString('Data kronologis kurang lengkap', $log->note);
    }

    #[Test]
    public function log_request_revision_oleh_manrisk()
    {
        $report = $this->createApprovedReport();

        $this->actingAs($this->manrisk);
        $response = $this->post(route('risk_reports.request_revision', $report->id), [
            'revision_note' => 'Mohon diperbaiki data dampak finansialnya, masih kurang sesuai.',
        ]);

        $response->assertSessionHas('success');

        $log = RiskReportLog::where('risk_report_id', $report->id)
            ->where('status_after_note', 'need_revision')
            ->latest()
            ->first();

        $this->assertNotNull($log, 'Log request revision harus tercatat');
        $this->assertStringContainsString('Revisi diminta oleh ManRisk', $log->note);
        $this->assertEquals($this->manrisk->id, $log->user_id);
    }

    #[Test]
    public function log_submit_revision_menyimpan_old_data_sebelum_update()
    {
        $report = $this->createApprovedReport();

        // ManRisk minta revisi
        $this->actingAs($this->manrisk);
        $this->post(route('risk_reports.request_revision', $report->id), [
            'revision_note' => 'Mohon diperbaiki kronologis kejadiannya.',
        ]);

        // Teller submit revisi
        $this->actingAs($this->teller);
        $response = $this->post(route('risk_reports.submit_revision', $report->id), [
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
        ]);

        $response->assertSessionHas('success');

        $log = RiskReportLog::where('risk_report_id', $report->id)
            ->where('note', 'Revisi laporan telah dikirim')
            ->first();

        $this->assertNotNull($log, 'Log submit revision harus tercatat');
        $this->assertNotNull($log->old_data, 'old_data harus terisi saat submit revision');
        
        $oldData = json_decode($log->old_data, true);
        $this->assertIsArray($oldData);
        $this->assertArrayHasKey('kronologis_kejadian', $oldData);
    }

    #[Test]
    public function log_approve_revision_oleh_manrisk()
    {
        $report = $this->createReportInRevisionFlow();

        $this->actingAs($this->manrisk);
        $response = $this->post(route('risk_reports.approve_revision', $report->id));

        $response->assertSessionHas('success');

        $log = RiskReportLog::where('risk_report_id', $report->id)
            ->where('note', 'Revisi disetujui oleh ManRisk')
            ->first();

        $this->assertNotNull($log, 'Log approve revision harus tercatat');
        $this->assertEquals($this->manrisk->id, $log->user_id);
        $this->assertEquals('approved', $log->status_after_note);
    }

    #[Test]
    public function log_add_progress_menyimpan_note_dengan_benar()
    {
        $report = $this->createApprovedReport();

        $this->actingAs($this->teller);
        $response = $this->post(route('risk_reports.add_progress', $report->id), [
            'note' => 'Sedang melakukan investigasi penyebab kejadian.',
            'new_status' => 'in_progress',
        ]);

        $response->assertSessionHas('success');

        $log = RiskReportLog::where('risk_report_id', $report->id)
            ->where('note', 'Sedang melakukan investigasi penyebab kejadian.')
            ->first();

        $this->assertNotNull($log, 'Log add progress harus tercatat');
        $this->assertEquals($this->teller->id, $log->user_id);
        $this->assertEquals('in_progress', $log->status_after_note);
    }

    #[Test]
    public function log_close_report_oleh_kacab()
    {
        $report = $this->createApprovedReport();

        $this->actingAs($this->kacab);
        $response = $this->post(route('risk_reports.add_progress', $report->id), [
            'note' => 'Laporan telah selesai ditindaklanjuti.',
            'new_status' => 'closed',
        ]);

        $response->assertSessionHas('success');

        $log = RiskReportLog::where('risk_report_id', $report->id)
            ->where('status_after_note', 'closed')
            ->first();

        $this->assertNotNull($log, 'Log close report harus tercatat');
        $this->assertEquals($this->kacab->id, $log->user_id);
        $this->assertEquals('closed', $log->status_after_note);
    }

    // ========================================================================
    // 5.2 DAILY LOG — Log::channel('daily')
    // ========================================================================

    #[Test]
    public function export_csv_mencatat_log_audit()
    {
        $report = $this->createApprovedReport();

        // Spy Log facade
        Log::shouldReceive('channel')
            ->with('daily')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[AUDIT] User export CSV')
                    && isset($context['user_id'])
                    && isset($context['user_name'])
                    && isset($context['role'])
                    && isset($context['total_reports'])
                    && isset($context['filters']);
            });

        $this->actingAs($this->kacab);
        $response = $this->get(route('risk.export', [
            'kategori' => 'finansial',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    #[Test]
    public function violate_declaration_mencatat_log_audit()
    {
        // Buat deklarasi oleh Kacab
        $this->actingAs($this->kacab);
        $this->post(route('risk_free_declarations.store'), [
            'statement_text' => 'Saya menyatakan bahwa tidak ada laporan risiko pada periode ini.',
            'jabatan' => [
                'Teller' => ['is_clean' => true, 'keterangan' => null],
                'CA' => ['is_clean' => true, 'keterangan' => null],
            ],
        ]);

        $declaration = RiskFreeDeclaration::where('branch_id', $this->branch->id)->first();
        $this->assertNotNull($declaration);

        // Spy Log
        Log::shouldReceive('channel')
            ->with('daily')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->withArgs(function ($message, $context) use ($declaration) {
                return str_contains($message, '[AUDIT] Declaration violated by ManRisk')
                    && isset($context['declaration_id'])
                    && $context['declaration_id'] === $declaration->id
                    && isset($context['branch_id'])
                    && isset($context['periode']);
            });

        // ManRisk violate
        $this->actingAs($this->manrisk);
        $response = $this->post(route('risk_free_declarations.violate', $declaration->id));

        $response->assertSessionHas('success');
    }

    #[Test]
    public function update_resolution_mencatat_log_audit()
    {
        $report = $this->createApprovedReport();

        Log::shouldReceive('channel')
            ->with('daily')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->withArgs(function ($message, $context) use ($report) {
                return str_contains($message, '[AUDIT] Resolution status updated')
                    && isset($context['report_id'])
                    && $context['report_id'] === $report->id
                    && isset($context['old_status'])
                    && isset($context['new_status'])
                    && $context['new_status'] === 'in_progress';
            });

        $this->actingAs($this->kacab);
        $response = $this->post(route('risk_reports.update_resolution', $report->id), [
            'resolution_status' => 'in_progress',
        ]);

        $response->assertSessionHas('success');
    }

    #[Test]
    public function error_di_store_mencatat_error_log()
    {
        Log::shouldReceive('channel')
            ->with('daily')
            ->andReturnSelf()
            ->shouldReceive('error')
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[ERROR] Gagal menyimpan laporan')
                    && isset($context['user_id'])
                    && isset($context['message']);
            });

        $this->actingAs($this->teller);

        // Kirim data invalid — risk_item_id yang tidak ada
        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => 99999, // tidak ada
            'risk_cause_id' => $this->riskCause->id,
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            'status_awal' => 'open',
        ]);

        // Harusnya redirect back dengan validation error (karena store gagal)
        $response->assertSessionHasErrors();
    }

    // ========================================================================
    // 5.3 ERROR HANDLING
    // ========================================================================

    #[Test]
    public function error_di_store_mengembalikan_pesan_error_yang_ramah()
    {
        $this->actingAs($this->teller);

        $response = $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => 99999,
            'risk_cause_id' => $this->riskCause->id,
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            'status_awal' => 'open',
        ]);

        // Validasi gagal karena risk_item_id tidak valid
        $response->assertSessionHasErrors(['risk_item_id']);
    }

    // ========================================================================
    // 5.4 ACTIVITY LOG — Detail Context
    // ========================================================================

    #[Test]
    public function export_csv_log_mengandung_detail_context_lengkap()
    {
        $report = $this->createApprovedReport();

        Log::shouldReceive('channel')
            ->with('daily')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->withArgs(function ($message, $context) {
                return isset($context['user_id'])
                    && isset($context['user_name'])
                    && isset($context['role'])
                    && isset($context['filename'])
                    && isset($context['total_reports'])
                    && isset($context['filters'])
                    && isset($context['ip']);
            });

        $this->actingAs($this->kacab);
        $response = $this->get(route('risk.export'));
        $response->assertOk();
    }

    #[Test]
    public function violate_declaration_log_mengandung_detail_context_lengkap()
    {
        // Buat deklarasi
        $this->actingAs($this->kacab);
        $this->post(route('risk_free_declarations.store'), [
            'statement_text' => 'Saya menyatakan bahwa tidak ada laporan risiko pada periode ini.',
            'jabatan' => [
                'Teller' => ['is_clean' => true, 'keterangan' => null],
                'CA' => ['is_clean' => true, 'keterangan' => null],
            ],
        ]);

        $declaration = RiskFreeDeclaration::where('branch_id', $this->branch->id)->first();

        Log::shouldReceive('channel')
            ->with('daily')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->withArgs(function ($message, $context) use ($declaration) {
                return isset($context['declaration_id'])
                    && isset($context['branch_id'])
                    && isset($context['periode'])
                    && isset($context['bulan'])
                    && isset($context['tahun'])
                    && isset($context['user_id'])
                    && isset($context['user_name']);
            });

        $this->actingAs($this->manrisk);
        $response = $this->post(route('risk_free_declarations.violate', $declaration->id));
        $response->assertSessionHas('success');
    }

    #[Test]
    public function update_resolution_log_mengandung_detail_context_lengkap()
    {
        $report = $this->createApprovedReport();

        Log::shouldReceive('channel')
            ->with('daily')
            ->andReturnSelf()
            ->shouldReceive('info')
            ->withArgs(function ($message, $context) use ($report) {
                return isset($context['user_id'])
                    && isset($context['user_name'])
                    && isset($context['report_id'])
                    && isset($context['kode_laporan'])
                    && isset($context['old_status'])
                    && isset($context['new_status']);
            });

        $this->actingAs($this->kacab);
        $response = $this->post(route('risk_reports.update_resolution', $report->id), [
            'resolution_status' => 'in_progress',
        ]);
        $response->assertSessionHas('success');
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    private function createReportByTeller(): RiskReport
    {
        $this->actingAs($this->teller);
        $this->post(route('form.risiko.store'), [
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->riskCause->id,
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
            'status_awal' => 'open',
        ]);

        return RiskReport::where('user_id', $this->teller->id)->first();
    }

    private function createApprovedReport(): RiskReport
    {
        $report = $this->createReportByTeller();

        $this->actingAs($this->kacab);
        $response = $this->post(route('risk_reports.update_status', $report->id), [
            'status' => 'approved',
        ]);
        $response->assertSessionHas('success');

        return $report->fresh();
    }

    private function createReportInRevisionFlow(): RiskReport
    {
        $report = $this->createApprovedReport();

        // ManRisk minta revisi
        $this->actingAs($this->manrisk);
        $this->post(route('risk_reports.request_revision', $report->id), [
            'revision_note' => 'Mohon diperbaiki kronologis kejadiannya.',
        ]);

        // Teller submit revisi
        $this->actingAs($this->teller);
        $this->post(route('risk_reports.submit_revision', $report->id), [
            'kronologis_kejadian' => self::KRONOLOGIS_20_KATA,
            'dampak_finansial' => 5000000,
            'skala_dampak' => 'sedang',
            'durasi_penyelesaian' => 3,
            'durasi_satuan' => 'hari',
        ]);

        return $report->fresh();
    }
}
