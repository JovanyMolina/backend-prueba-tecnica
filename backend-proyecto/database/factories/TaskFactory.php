<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'=>fake()->sentence(4),
            'description'=>fake()->paragraph(),
            'priority'=>fake()->randomElement(['Alta','Media','Baja']),
            'due_date'=>now()->addDays(rand(1,30)),
            'state'=>fake()->randomElement(['Pendiente','En progreso','Hecha']),
        ];
    }
}
