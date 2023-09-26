<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
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
    public function definition()
    {
        return [
            'status' => $this->faker->randomElement(['todo', 'done']),
            'priority' => $this->faker->numberBetween(1, 5),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'completed_at' => $this->faker->optional(0.5)->dateTimeBetween('-1 year', 'now'), // 50% chance of completion
            'user_id' => User::factory(),
        ];
    }
}
