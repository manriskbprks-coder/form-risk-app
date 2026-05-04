<?php

namespace Database\Factories;

use App\Models\RiskReport;
use App\Models\User;
use App\Models\Branch;
use App\Models\RiskItem;
use App\Models\RiskCause;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiskReport>
 */
class RiskReportFactory extends Factory
{
    protected $model = RiskReport::class;

    public function definition(): array
    {
        $branch = Branch::factory()->create();
        $user = User::factory()->create(['branch_id' => $branch->id]);
        $item = RiskItem::factory()->create();
        $cause = RiskCause::factory()->create(['risk_item_id' => $item->id]);

        return [
            'kode_laporan' => 'RISK-TEST-' . fake()->unique()->randomNumber(5),
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'tanggal_kejadian' => now()->subDays(rand(1, 30)),
            'tanggal_diketahui' => now()->subDays(rand(0, 5)),
            'risk_item_id' => $item->id,
            'risk_cause_id' => $cause->id,
            'kronologis_kejadian' => 'Kejadian ini terjadi pada saat jam operasional dimana terdapat selisih yang cukup signifikan setelah dilakukan pengecekan ulang oleh supervisor.',
            'kategori' => fake()->randomElement(['finansial', 'non-finansial']),
            'dampak_finansial' => fake()->optional(0.7)->numberBetween(100000, 10000000),
            'approval_status' => 'pending_kacab',
            'resolution_status' => 'open',
        ];
    }
}
