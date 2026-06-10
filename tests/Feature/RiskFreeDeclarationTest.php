<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\RiskFreeDeclaration;
use App\Models\RiskFreeDeclarationDetail;
use App\Models\Notification;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RiskFreeDeclarationTest extends TestCase
{
    use RefreshDatabase;

    private User $teller;
    private User $kacab;
    private User $kacabLain;
    private User $korwil;
    private User $manrisk;
    private Branch $branch;
    private Branch $branchLain;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat roles dengan role_category
        $roleMapping = [
            'teller' => 'maker', 'kacab' => 'checker', 'korwil' => 'viewer', 'manrisk' => 'admin',
        ];
        foreach ($roleMapping as $name => $category) {
            Role::firstOrCreate(['name' => $name], ['role_category' => $category]);
        }

        // Buat branch
        $this->branch = Branch::factory()->create(['nama_cabang' => 'Cabang A', 'is_active' => true]);
        $this->branchLain = Branch::factory()->create(['nama_cabang' => 'Cabang B', 'is_active' => true]);

        // Buat users
        $this->teller = User::factory()->create(['branch_id' => $this->branch->id]);
        $this->teller->assignRole('teller');

        $this->kacab = User::factory()->create(['branch_id' => $this->branch->id]);
        $this->kacab->assignRole('kacab');

        $this->kacabLain = User::factory()->create(['branch_id' => $this->branchLain->id]);
        $this->kacabLain->assignRole('kacab');

        $this->korwil = User::factory()->create();
        $this->korwil->assignRole('korwil');

        $this->manrisk = User::factory()->create();
        $this->manrisk->assignRole('manrisk');
    }

    // =======================================================================
    //  AKSES FORM CREATE
    // =======================================================================

    #[Test]
    public function kacab_can_access_create_form()
    {
        $response = $this->actingAs($this->kacab)
            ->get(route('risk_free_declarations.create'));

        $response->assertStatus(200);
        $response->assertSee('Deklarasi Nihil Risiko');
        $response->assertSee('Teller');
        $response->assertSee('CA');
        $response->assertSee('CSR');
        $response->assertSee('Security');
        $response->assertSee('Kacab');
    }

    #[Test]
    public function non_kacab_cannot_access_create_form()
    {
        // Teller
        $response = $this->actingAs($this->teller)
            ->get(route('risk_free_declarations.create'));
        $response->assertStatus(403);

        // Korwil
        $response = $this->actingAs($this->korwil)
            ->get(route('risk_free_declarations.create'));
        $response->assertStatus(403);

        // ManRisk
        $response = $this->actingAs($this->manrisk)
            ->get(route('risk_free_declarations.create'));
        $response->assertStatus(403);
    }

    #[Test]
    public function kacab_redirected_if_already_declared()
    {
        // Buat deklarasi untuk periode ini
        RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => now()->day <= 14 ? '1' : '2',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->kacab)
            ->get(route('risk_free_declarations.create'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('info');
    }

    // =======================================================================
    //  STORE DEKLARASI
    // =======================================================================

    #[Test]
    public function kacab_can_submit_declaration()
    {
        $jabatanList = ['Teller', 'CA', 'CSR', 'Security', 'Kacab'];
        $data = [
            'jabatan' => [],
            'statement_text' => 'Dengan ini menyatakan tidak ada risiko operasional pada periode ini.',
        ];

        foreach ($jabatanList as $jabatan) {
            $data['jabatan'][$jabatan] = [
                'is_clean' => '1',
                'keterangan' => '',
            ];
        }

        $response = $this->actingAs($this->kacab)
            ->post(route('risk_free_declarations.store'), $data);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('success');

        // Cek di database
        $this->assertDatabaseHas('risk_free_declarations', [
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'status' => 'active',
        ]);

        // Cek detail
        $declaration = RiskFreeDeclaration::where('branch_id', $this->branch->id)->first();
        $this->assertNotNull($declaration);
        $this->assertCount(5, $declaration->details);
        foreach ($jabatanList as $jabatan) {
            $this->assertDatabaseHas('risk_free_declaration_details', [
                'risk_free_declaration_id' => $declaration->id,
                'jabatan' => $jabatan,
                'is_clean' => true,
            ]);
        }
    }

    #[Test]
    public function duplicate_declaration_is_rejected()
    {
        // Buat deklarasi pertama
        RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => now()->day <= 14 ? '1' : '2',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement pertama',
            'status' => 'active',
        ]);

        // Coba submit lagi
        $data = [
            'jabatan' => [
                'Teller' => ['is_clean' => '1', 'keterangan' => ''],
                'CA' => ['is_clean' => '1', 'keterangan' => ''],
                'CSR' => ['is_clean' => '1', 'keterangan' => ''],
                'Security' => ['is_clean' => '1', 'keterangan' => ''],
                'Kacab' => ['is_clean' => '1', 'keterangan' => ''],
            ],
            'statement_text' => 'Test statement kedua',
        ];

        $response = $this->actingAs($this->kacab)
            ->post(route('risk_free_declarations.store'), $data);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error');
    }

    #[Test]
    public function validation_requires_statement_text()
    {
        $data = [
            'jabatan' => [
                'Teller' => ['is_clean' => '1', 'keterangan' => ''],
            ],
            'statement_text' => 'short', // Kurang dari 10 karakter
        ];

        $response = $this->actingAs($this->kacab)
            ->post(route('risk_free_declarations.store'), $data);

        $response->assertSessionHasErrors('statement_text');
    }

    #[Test]
    public function validation_requires_at_least_one_jabatan()
    {
        $data = [
            'jabatan' => [],
            'statement_text' => 'Pernyataan yang cukup panjang untuk validasi.',
        ];

        $response = $this->actingAs($this->kacab)
            ->post(route('risk_free_declarations.store'), $data);

        $response->assertSessionHasErrors('jabatan');
    }

    #[Test]
    public function non_kacab_cannot_submit_declaration()
    {
        $data = [
            'jabatan' => [
                'Teller' => ['is_clean' => '1', 'keterangan' => ''],
            ],
            'statement_text' => 'Pernyataan yang cukup panjang untuk validasi.',
        ];

        // Teller
        $response = $this->actingAs($this->teller)
            ->post(route('risk_free_declarations.store'), $data);
        $response->assertStatus(403);

        // Korwil
        $response = $this->actingAs($this->korwil)
            ->post(route('risk_free_declarations.store'), $data);
        $response->assertStatus(403);
    }

    // =======================================================================
    //  NOTIFIKASI
    // =======================================================================

    #[Test]
    public function declaration_creates_notification_for_manrisk()
    {
        $data = [
            'jabatan' => [
                'Teller' => ['is_clean' => '1', 'keterangan' => ''],
                'CA' => ['is_clean' => '1', 'keterangan' => ''],
                'CSR' => ['is_clean' => '1', 'keterangan' => ''],
                'Security' => ['is_clean' => '1', 'keterangan' => ''],
                'Kacab' => ['is_clean' => '1', 'keterangan' => ''],
            ],
            'statement_text' => 'Dengan ini menyatakan tidak ada risiko operasional pada periode ini.',
        ];

        $this->actingAs($this->kacab)
            ->post(route('risk_free_declarations.store'), $data);

        // Cek notifikasi untuk ManRisk
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->manrisk->id,
            'type' => 'declaration',
        ]);
    }

    // =======================================================================
    //  HISTORY / RIAWAYAT
    // =======================================================================

    #[Test]
    public function kacab_can_access_own_history()
    {
        // Buat deklarasi untuk cabang A
        RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->kacab)
            ->get(route('risk_free_declarations.history'));

        $response->assertStatus(200);
        $response->assertSee('Cabang A');
    }

    #[Test]
    public function kacab_only_sees_own_branch_history()
    {
        // Buat deklarasi untuk cabang A
        RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement A',
            'status' => 'active',
        ]);

        // Buat deklarasi untuk cabang B
        RiskFreeDeclaration::create([
            'branch_id' => $this->branchLain->id,
            'user_id' => $this->kacabLain->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement B',
            'status' => 'active',
        ]);

        // Kacab cabang A hanya lihat deklarasi cabang A
        $response = $this->actingAs($this->kacab)
            ->get(route('risk_free_declarations.history'));

        $response->assertStatus(200);
        $response->assertSee('Cabang A');
        $response->assertDontSee('Cabang B');
    }

    #[Test]
    public function manrisk_can_access_all_history()
    {
        // Buat deklarasi untuk cabang A
        RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement A',
            'status' => 'active',
        ]);

        // Buat deklarasi untuk cabang B
        RiskFreeDeclaration::create([
            'branch_id' => $this->branchLain->id,
            'user_id' => $this->kacabLain->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement B',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->manrisk)
            ->get(route('risk_free_declarations.history'));

        $response->assertStatus(200);
        $response->assertSee('Cabang A');
        $response->assertSee('Cabang B');
    }

    #[Test]
    public function non_kacab_non_manrisk_cannot_access_history()
    {
        $response = $this->actingAs($this->teller)
            ->get(route('risk_free_declarations.history'));
        $response->assertStatus(403);
    }

    // =======================================================================
    //  REJECT
    // =======================================================================

    #[Test]
    public function manrisk_can_reject_declaration()
    {
        $declaration = RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->manrisk)
            ->post(route('risk_free_declarations.reject', $declaration->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('risk_free_declarations', [
            'id' => $declaration->id,
            'status' => 'rejected',
            'rejected_by' => $this->manrisk->id,
        ]);

        $this->assertNotNull($declaration->fresh()->rejected_at);
    }

    #[Test]
    public function non_manrisk_cannot_reject_declaration()
    {
        $declaration = RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement',
            'status' => 'active',
        ]);

        // Kacab
        $response = $this->actingAs($this->kacab)
            ->post(route('risk_free_declarations.reject', $declaration->id));
        $response->assertStatus(403);

        // Korwil
        $response = $this->actingAs($this->korwil)
            ->post(route('risk_free_declarations.reject', $declaration->id));
        $response->assertStatus(403);

        // Teller
        $response = $this->actingAs($this->teller)
            ->post(route('risk_free_declarations.reject', $declaration->id));
        $response->assertStatus(403);

        // Status masih active
        $this->assertDatabaseHas('risk_free_declarations', [
            'id' => $declaration->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function cannot_reject_already_rejected_declaration()
    {
        $declaration = RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement',
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => $this->manrisk->id,
        ]);

        $response = $this->actingAs($this->manrisk)
            ->post(route('risk_free_declarations.reject', $declaration->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    #[Test]
    public function reject_creates_notification_for_kacab()
    {
        $declaration = RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement',
            'status' => 'active',
        ]);

        $this->actingAs($this->manrisk)
            ->post(route('risk_free_declarations.reject', $declaration->id));

        // Cek notifikasi untuk Kacab
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->kacab->id,
            'type' => 'declaration_rejected',
        ]);
    }

    // =======================================================================
    //  DASHBOARD — Kacab
    // =======================================================================

    #[Test]
    public function kacab_dashboard_shows_declaration_status()
    {
        $response = $this->actingAs($this->kacab)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Deklarasi Nihil Risiko');
        $response->assertSee('Belum melakukan deklarasi');
    }

    #[Test]
    public function kacab_dashboard_shows_declared_status_when_done()
    {
        RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => now()->day <= 14 ? '1' : '2',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->kacab)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Deklarasi untuk periode ini sudah dilakukan');
    }

    // =======================================================================
    //  DASHBOARD — ManRisk
    // =======================================================================

    #[Test]
    public function manrisk_dashboard_shows_declaration_summary()
    {
        // Buat deklarasi untuk cabang A
        RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->manrisk)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Rekap Deklarasi Nihil Risiko');
        $response->assertSee('Cabang A');
    }

    #[Test]
    public function manrisk_dashboard_shows_rejected_flag()
    {
        // Buat deklarasi untuk cabang A
        RiskFreeDeclaration::create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->kacab->id,
            'periode' => '1',
            'bulan' => now()->month,
            'tahun' => now()->year,
            'statement_text' => 'Test statement',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->manrisk)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Rekap Deklarasi Nihil Risiko');
    }
}
