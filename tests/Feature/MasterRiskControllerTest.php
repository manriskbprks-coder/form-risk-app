<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RiskItem;
use App\Models\RiskCause;
use App\Models\RiskMitigation;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MasterRiskControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $manriskUser;
    private User $nonManriskUser;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'manrisk', 'role_category' => 'admin']);
        Role::firstOrCreate(['name' => 'kacab', 'role_category' => 'checker']);

        $this->manriskUser = User::factory()->create();
        $this->manriskUser->assignRole('manrisk');

        $this->nonManriskUser = User::factory()->create();
        $this->nonManriskUser->assignRole('kacab');
    }

    // -----------------------------------------------------------------------
    //  AUTHORIZATION — SAD PATHS
    // -----------------------------------------------------------------------

    #[Test]
    public function user_without_manrisk_role_cannot_view_the_index_page()
    {
        $this->actingAs($this->nonManriskUser)
            ->get(route('admin.risk_master.index'))
            ->assertForbidden();
    }

    #[Test]
    public function user_without_manrisk_role_cannot_store_a_cause()
    {
        $item = RiskItem::factory()->create();

        $this->actingAs($this->nonManriskUser)
            ->post(route('admin.risk_master.store_cause', $item->id), [
                'penyebab' => 'Kelalaian',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function user_without_manrisk_role_cannot_store_a_mitigation()
    {
        $cause = RiskCause::factory()->create();

        $this->actingAs($this->nonManriskUser)
            ->post(route('admin.risk_master.store_mitigation', $cause->id), [
                'mitigasi' => 'Cek ulang dokumen',
            ])
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    //  VALIDATION — SAD PATHS (MANRISK USER)
    // -----------------------------------------------------------------------

    #[Test]
    public function store_cause_validates_required_fields()
    {
        $item = RiskItem::factory()->create();

        $this->actingAs($this->manriskUser)
            ->from(route('admin.risk_master.index'))
            ->post(route('admin.risk_master.store_cause', $item->id), [
                'penyebab' => '',
            ])
            ->assertRedirect(route('admin.risk_master.index'))
            ->assertSessionHasErrors('penyebab');

        $this->assertDatabaseMissing('risk_causes', [
            'risk_item_id' => $item->id,
        ]);
    }

    #[Test]
    public function store_mitigation_validates_required_fields()
    {
        $cause = RiskCause::factory()->create();

        $this->actingAs($this->manriskUser)
            ->from(route('admin.risk_master.index'))
            ->post(route('admin.risk_master.store_mitigation', $cause->id), [
                'mitigasi' => '',
            ])
            ->assertRedirect(route('admin.risk_master.index'))
            ->assertSessionHasErrors('mitigasi');

        $this->assertDatabaseMissing('risk_mitigations', [
            'risk_cause_id' => $cause->id,
        ]);
    }

    #[Test]
    public function store_cause_validates_max_string_length()
    {
        $item = RiskItem::factory()->create();

        $this->actingAs($this->manriskUser)
            ->from(route('admin.risk_master.index'))
            ->post(route('admin.risk_master.store_cause', $item->id), [
                'penyebab' => str_repeat('a', 256),
                'sumber_risiko' => 'manusia',
            ])
            ->assertRedirect(route('admin.risk_master.index'))
            ->assertSessionHasErrors('penyebab');
    }

    #[Test]
    public function store_mitigation_validates_max_string_length()
    {
        $cause = RiskCause::factory()->create();

        $this->actingAs($this->manriskUser)
            ->from(route('admin.risk_master.index'))
            ->post(route('admin.risk_master.store_mitigation', $cause->id), [
                'mitigasi' => str_repeat('a', 256),
            ])
            ->assertRedirect(route('admin.risk_master.index'))
            ->assertSessionHasErrors('mitigasi');
    }

    // -----------------------------------------------------------------------
    //  EXECUTION — HAPPY PATHS (MANRISK USER)
    // -----------------------------------------------------------------------

    #[Test]
    public function manrisk_user_can_access_index_page()
    {
        $response = $this->actingAs($this->manriskUser)
            ->get(route('admin.risk_master.index'));

        $response->assertOk();
    }

    #[Test]
    public function manrisk_user_can_store_a_new_cause_and_persist_to_database()
    {
        $item = RiskItem::factory()->create();

        $this->actingAs($this->manriskUser)
            ->post(route('admin.risk_master.store_cause', $item->id), [
                'penyebab' => 'Kelalaian Operasional',
                'sumber_risiko' => 'manusia',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('risk_causes', [
            'risk_item_id' => $item->id,
            'penyebab' => 'Kelalaian Operasional',
        ]);
    }

    #[Test]
    public function manrisk_user_can_store_a_new_mitigation_and_persist_to_database()
    {
        $cause = RiskCause::factory()->create();

        $this->actingAs($this->manriskUser)
            ->post(route('admin.risk_master.store_mitigation', $cause->id), [
                'mitigasi' => 'Double check oleh supervisor',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('risk_mitigations', [
            'risk_cause_id' => $cause->id,
            'mitigasi' => 'Double check oleh supervisor',
        ]);
    }

    #[Test]
    public function manrisk_user_can_store_multiple_causes_under_the_same_risk_item()
    {
        $item = RiskItem::factory()->create();

        $this->actingAs($this->manriskUser)
            ->post(route('admin.risk_master.store_cause', $item->id), [
                'penyebab' => 'Penyebab Pertama',
                'sumber_risiko' => 'manusia',
            ]);
        $this->actingAs($this->manriskUser)
            ->post(route('admin.risk_master.store_cause', $item->id), [
                'penyebab' => 'Penyebab Kedua',
                'sumber_risiko' => 'proses_internal',
            ]);

        $this->assertDatabaseHas('risk_causes', [
            'risk_item_id' => $item->id,
            'penyebab' => 'Penyebab Pertama',
        ]);
        $this->assertDatabaseHas('risk_causes', [
            'risk_item_id' => $item->id,
            'penyebab' => 'Penyebab Kedua',
        ]);
    }

    #[Test]
    public function manrisk_user_can_store_multiple_mitigations_under_the_same_cause()
    {
        $cause = RiskCause::factory()->create();

        $this->actingAs($this->manriskUser)
            ->post(route('admin.risk_master.store_mitigation', $cause->id), [
                'mitigasi' => 'Mitigasi Pertama',
            ]);

        $this->actingAs($this->manriskUser)
            ->post(route('admin.risk_master.store_mitigation', $cause->id), [
                'mitigasi' => 'Mitigasi Kedua',
            ]);

        $this->assertDatabaseHas('risk_mitigations', [
            'risk_cause_id' => $cause->id,
            'mitigasi' => 'Mitigasi Pertama',
        ]);
        $this->assertDatabaseHas('risk_mitigations', [
            'risk_cause_id' => $cause->id,
            'mitigasi' => 'Mitigasi Kedua',
        ]);
    }

    #[Test]
    public function store_cause_fails_when_risk_item_does_not_exist()
    {
        $this->actingAs($this->manriskUser)
            ->post(route('admin.risk_master.store_cause', 99999), [
                'penyebab' => 'Akan gagal karena FK constraint',
                'sumber_risiko' => 'manusia',
            ])
            ->assertStatus(500);
    }

    #[Test]
    public function store_mitigation_fails_when_cause_does_not_exist()
    {
        $this->actingAs($this->manriskUser)
            ->post(route('admin.risk_master.store_mitigation', 99999), [
                'mitigasi' => 'Akan gagal karena FK constraint',
            ])
            ->assertStatus(500);
    }

    #[Test]
    public function index_page_query_loads_with_eager_relationships()
    {
        $item = RiskItem::factory()
            ->has(RiskCause::factory()->has(RiskMitigation::factory(), 'mitigations'), 'causes')
            ->create(['role_target' => 'kacab']);

        $items = RiskItem::with('causes.mitigations')
            ->where('role_target', 'kacab')
            ->orderBy('kategori')
            ->get();

        $this->assertCount(1, $items);
        $this->assertTrue($items->first()->relationLoaded('causes'));
        $this->assertTrue($items->first()->causes->first()->relationLoaded('mitigations'));
    }

    // -----------------------------------------------------------------------
    //  STORE ITEM — HAPPY & SAD PATHS
    // -----------------------------------------------------------------------

    #[Test]
    public function manrisk_user_can_store_a_new_risk_item()
    {
        $this->actingAs($this->manriskUser)
            ->post(route('admin.risk_master.store_item'), [
                'nama_risiko' => 'Kehilangan Uang Tunai',
                'kategori' => 'finansial',
                'sumber_risiko' => 'manusia',
                'role_target' => 'teller',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('risk_items', [
            'nama_risiko' => 'Kehilangan Uang Tunai',
            'kategori' => 'finansial',
            'sumber_risiko' => 'manusia',
            'role_target' => 'teller',
        ]);
    }

    #[Test]
    public function store_item_validates_required_fields()
    {
        $this->actingAs($this->manriskUser)
            ->from(route('admin.risk_master.index'))
            ->post(route('admin.risk_master.store_item'), [
                'nama_risiko' => '',
                'kategori' => '',
                'sumber_risiko' => '',
                'role_target' => '',
            ])
            ->assertRedirect(route('admin.risk_master.index'))
            ->assertSessionHasErrors(['nama_risiko', 'kategori', 'sumber_risiko', 'role_target']);
    }

    #[Test]
    public function store_item_validates_invalid_kategori()
    {
        $this->actingAs($this->manriskUser)
            ->from(route('admin.risk_master.index'))
            ->post(route('admin.risk_master.store_item'), [
                'nama_risiko' => 'Risiko Test',
                'kategori' => 'invalid',
                'sumber_risiko' => 'manusia',
                'role_target' => 'teller',
            ])
            ->assertRedirect(route('admin.risk_master.index'))
            ->assertSessionHasErrors('kategori');
    }

    #[Test]
    public function store_item_validates_invalid_sumber_risiko()
    {
        $this->actingAs($this->manriskUser)
            ->from(route('admin.risk_master.index'))
            ->post(route('admin.risk_master.store_item'), [
                'nama_risiko' => 'Risiko Test',
                'kategori' => 'finansial',
                'sumber_risiko' => 'invalid',
                'role_target' => 'teller',
            ])
            ->assertRedirect(route('admin.risk_master.index'))
            ->assertSessionHasErrors('sumber_risiko');
    }

    #[Test]
    public function store_item_validates_invalid_role_target()
    {
        $this->actingAs($this->manriskUser)
            ->from(route('admin.risk_master.index'))
            ->post(route('admin.risk_master.store_item'), [
                'nama_risiko' => 'Risiko Test',
                'kategori' => 'finansial',
                'sumber_risiko' => 'manusia',
                'role_target' => 'invalid',
            ])
            ->assertRedirect(route('admin.risk_master.index'))
            ->assertSessionHasErrors('role_target');
    }

    // -----------------------------------------------------------------------
    //  UPDATE CAUSE — HAPPY & SAD PATHS
    // -----------------------------------------------------------------------

    #[Test]
    public function manrisk_user_can_update_cause()
    {
        $cause = RiskCause::factory()->create([
            'penyebab' => 'Penyebab Lama',
            'sumber_risiko' => 'manusia',
        ]);

        $this->actingAs($this->manriskUser)
            ->patch(route('admin.risk_master.update_cause', $cause->id), [
                'penyebab' => 'Penyebab Baru',
                'sumber_risiko' => 'proses_internal',
                'mitigasi' => 'Mitigasi baru',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('risk_causes', [
            'id' => $cause->id,
            'penyebab' => 'Penyebab Baru',
            'sumber_risiko' => 'proses_internal',
        ]);

        $this->assertDatabaseHas('risk_mitigations', [
            'risk_cause_id' => $cause->id,
            'mitigasi' => 'Mitigasi baru',
        ]);
    }

    #[Test]
    public function update_cause_validates_required_fields()
    {
        $cause = RiskCause::factory()->create();

        $this->actingAs($this->manriskUser)
            ->from(route('admin.risk_master.index'))
            ->patch(route('admin.risk_master.update_cause', $cause->id), [
                'penyebab' => '',
                'sumber_risiko' => '',
            ])
            ->assertRedirect(route('admin.risk_master.index'))
            ->assertSessionHasErrors(['penyebab', 'sumber_risiko']);
    }

    #[Test]
    public function update_cause_fails_when_cause_does_not_exist()
    {
        $this->actingAs($this->manriskUser)
            ->patch(route('admin.risk_master.update_cause', 99999), [
                'penyebab' => 'Penyebab Baru',
                'sumber_risiko' => 'manusia',
            ])
            ->assertNotFound();
    }

    // -----------------------------------------------------------------------
    //  DESTROY ITEM — HAPPY & SAD PATHS
    // -----------------------------------------------------------------------

    #[Test]
    public function manrisk_user_can_destroy_risk_item()
    {
        $item = RiskItem::factory()->create();

        $this->actingAs($this->manriskUser)
            ->delete(route('admin.risk_master.destroy_item', $item->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('risk_items', ['id' => $item->id]);
    }

    #[Test]
    public function destroy_item_fails_when_item_does_not_exist()
    {
        $this->actingAs($this->manriskUser)
            ->delete(route('admin.risk_master.destroy_item', 99999))
            ->assertNotFound();
    }

    #[Test]
    public function user_without_manrisk_role_cannot_destroy_item()
    {
        $item = RiskItem::factory()->create();

        $this->actingAs($this->nonManriskUser)
            ->delete(route('admin.risk_master.destroy_item', $item->id))
            ->assertForbidden();
    }

    // -----------------------------------------------------------------------
    //  STORE CAUSE WITH MITIGASI — BUNDLING
    // -----------------------------------------------------------------------

    #[Test]
    public function manrisk_user_can_store_cause_with_mitigasi()
    {
        $item = RiskItem::factory()->create();

        $this->actingAs($this->manriskUser)
            ->post(route('admin.risk_master.store_cause', $item->id), [
                'penyebab' => 'Penyebab dengan mitigasi',
                'sumber_risiko' => 'sistem_teknologi',
                'mitigasi' => 'Mitigasi langsung',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('risk_causes', [
            'risk_item_id' => $item->id,
            'penyebab' => 'Penyebab dengan mitigasi',
            'sumber_risiko' => 'sistem_teknologi',
        ]);

        $this->assertDatabaseHas('risk_mitigations', [
            'mitigasi' => 'Mitigasi langsung',
        ]);
    }

    #[Test]
    public function store_cause_validates_sumber_risiko()
    {
        $item = RiskItem::factory()->create();

        $this->actingAs($this->manriskUser)
            ->from(route('admin.risk_master.index'))
            ->post(route('admin.risk_master.store_cause', $item->id), [
                'penyebab' => 'Penyebab',
                'sumber_risiko' => 'invalid',
            ])
            ->assertRedirect(route('admin.risk_master.index'))
            ->assertSessionHasErrors('sumber_risiko');
    }

    // -----------------------------------------------------------------------
    //  STORE ITEM — AUTHORIZATION
    // -----------------------------------------------------------------------

    #[Test]
    public function user_without_manrisk_role_cannot_store_item()
    {
        $this->actingAs($this->nonManriskUser)
            ->post(route('admin.risk_master.store_item'), [
                'nama_risiko' => 'Risiko Baru',
                'kategori' => 'finansial',
                'sumber_risiko' => 'manusia',
                'role_target' => 'teller',
            ])
            ->assertForbidden();
    }

    #[Test]
    public function user_without_manrisk_role_cannot_update_cause()
    {
        $cause = RiskCause::factory()->create();

        $this->actingAs($this->nonManriskUser)
            ->patch(route('admin.risk_master.update_cause', $cause->id), [
                'penyebab' => 'Update',
                'sumber_risiko' => 'manusia',
            ])
            ->assertForbidden();
    }
}
