<?php

namespace Database\Factories;

use App\Models\InfoautoCatalog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<InfoautoCatalog>
 */
class InfoautoCatalogFactory extends Factory
{
    protected $model = InfoautoCatalog::class;

    public function definition(): array
    {
        return [
            'source_system' => 'test',
            'codia' => 'codia-' . Str::random(10),
            'brand_name' => fake()->randomElement(['PEUGEOT', 'TOYOTA', 'RENAULT', 'FORD']),
            'model_name' => fake()->randomElement(['2008', 'COROLLA', 'DUSTER', 'RANGER']),
            'version_name_raw' => strtoupper(fake()->words(3, true)),
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ];
    }
}
