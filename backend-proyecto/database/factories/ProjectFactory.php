<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=>fake()->sentence(3),
            'description'=>fake()->paragraph(),
            'start_date'=>now()->subDays(rand(0,30)),
            'end_date'=>now()->addDays(rand(10,60)),
            'status'=>fake()->randomElement(['Activo','Pausado','Terminado']),
        ];
    }
}
