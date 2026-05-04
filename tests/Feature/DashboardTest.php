<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\RiskReport;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $teller;
    private User $kacab;
    private User $korwil;
    private User $manrisk;
    private Branch $branch;
    private Branch $branchLain;

    protected function setUp(): void
    {
        parent::setUp();

        collect(['teller', 'kacab', 'korwil', 'manrisk'])
            ->each(fn ($r) => Role::firstOrCreate(['name' => $r]));

        $this->branch = Branch::factory()->create(['nama_cabang' => 'Cabang A', 'is_active' => true]);
        $this->branchLain = Branch::factory()->create(['nama_cabang' => 'Cabang B', 'is_active' => true]);

        $this->teller = User::factory()->create(['branch_id' => $this->branch->id]);
        $this->teller->assignRole('teller');

        $this->kacab = User::factory()->create(['branch_id' => $this->branch->id]);
        $this->kacab->assignRole('kacab');

        $this->korwil = User::factory()->create();
        $this->korwil->assignRole('korwil');
        $this->branch->update(['korwil_id' => $this->korwil->id]);
        $this->branchLain->update(['korwil_id' => $this->korwil->id]);

        $this->manrisk = User::factory()->create();
        $this->manrisk->assignRole('manrisk');
    }

    // =======================================================================
    //  AUTH
    // =======================================================================

    #[Test]
    public function guest_cannot_access_dashboard()
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    // =======================================================================
    //  STAFF DASHBOARD
    // =======================================================================

    #[Test]
    public function staff_sees_only_own_reports_on_dashboard()
    {
        RiskReport::factory()->create(['user_id' => $this->teller->id, 'branch_id' => $this->branch->id]);
        RiskReport::factory()->create(['user_id' => User::factory()->create(['branch_id' => $this->branch->id])->id, 'branch_id' => $this->branch->id]);

        $response = $this->actingAs($this->teller)->get(route('dashboard'));
        $response->assertOk();

        $this->assertCount(1, $response->viewData('recentReports'));
    }

    #[Test]
    public function staff_sees_correct_stat_cards()
    {
        // Buat laporan bulan ini
        RiskReport::factory()->create([
            'user_id' => $this->teller->id,
            'branch_id' => $this->branch->id,
            'approval_status' => 'pending_kacab',
            'created_at' => now(),
        ]);

        RiskReport::factory()->create([
            'user_id' => $this->teller->id,
            'branch_id' => $this->branch->id,
            'approval_status' => 'approved',
            'kategori' => 'finansial',
            'dampak_finansial' => 2000000,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->teller)->get(route('dashboard'));
        $response->assertOk();

        $this->assertEquals(2, $response->viewData('totalLaporanBulanIni'));
        $this->assertEquals(1, $response->viewData('totalPending'));
        $this->assertEquals(1, $response->viewData('totalApproved'));
        $this->assertEquals(2000000, $response->viewData('totalLossApproved'));
    }

    // =======================================================================
    //  KACAB DASHBOARD
    // =======================================================================

    #[Test]
    public function kacab_sees_all_reports_from_own_branch()
    {
        RiskReport::factory()->create(['branch_id' => $this->branch->id, 'user_id' => $this->teller->id]);
        RiskReport::factory()->create(['branch_id' => $this->branchLain->id, 'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id]);

        $response = $this->actingAs($this->kacab)->get(route('dashboard'));
        $response->assertOk();

        $this->assertCount(1, $response->viewData('recentReports'));
    }

    #[Test]
    public function kacab_sees_pending_count_badge()
    {
        RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'approval_status' => 'pending_kacab',
        ]);
        RiskReport::factory()->create([
            'branch_id' => $this->branch->id,
            'user_id' => $this->teller->id,
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->kacab)->get(route('dashboard'));
        $response->assertOk();

        $this->assertEquals(1, $response->viewData('pendingCount'));
    }

    // =======================================================================
    //  KORWIL DASHBOARD
    // =======================================================================

    #[Test]
    public function korwil_sees_reports_from_all_supervised_branches()
    {
        RiskReport::factory()->create(['branch_id' => $this->branch->id, 'user_id' => $this->teller->id]);
        RiskReport::factory()->create(['branch_id' => $this->branchLain->id, 'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id]);

        $response = $this->actingAs($this->korwil)->get(route('dashboard'));
        $response->assertOk();

        $this->assertCount(2, $response->viewData('recentReports'));
    }

    // =======================================================================
    //  MANRISK DASHBOARD
    // =======================================================================

    #[Test]
    public function manrisk_sees_all_reports()
    {
        RiskReport::factory()->create(['branch_id' => $this->branch->id, 'user_id' => $this->teller->id]);
        RiskReport::factory()->create(['branch_id' => $this->branchLain->id, 'user_id' => User::factory()->create(['branch_id' => $this->branchLain->id])->id]);

        $response = $this->actingAs($this->manrisk)->get(route('dashboard'));
        $response->assertOk();

        $this->assertCount(2, $response->viewData('recentReports'));
    }

    #[Test]
    public function dashboard_returns_200_for_all_roles()
    {
        $this->actingAs($this->teller)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->kacab)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->korwil)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->manrisk)->get(route('dashboard'))->assertOk();
    }
}
