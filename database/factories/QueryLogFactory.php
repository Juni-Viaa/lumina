<?php

namespace Database\Factories;

use App\Models\QueryLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QueryLog>
 */
class QueryLogFactory extends Factory
{
    protected $model = QueryLog::class;

    public function definition(): array
    {
        $text = fake()->sentence();

        return [
            'user_id'           => User::factory(),
            'query_text'        => $text,
            'query_title'       => $text,
            'status'            => 'answered',
            'response_time_ms'  => fake()->numberBetween(100, 5000),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending', 'response_time_ms' => null]);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed']);
    }
}