<?php

namespace Database\Factories;

use App\Models\RiskItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiskItem>
 */
class RiskItemFactory extends Factory
{
    protected $model = RiskItem::class;

    public function definition(): array
    {
        return [
            'nama_risiko' => fake()->sentence(3),
            'kategori' => fake()->randomElement(['finansial', 'non-finansial']),
            'role_target' => \Spatie\Permission\Models\Role::inRandomOrder()->first()?->name ?? 'maker_default',
        ];
    }
}
