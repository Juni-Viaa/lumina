<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\QueryLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        return [
            'query_id'    => QueryLog::factory(),
            'answer_text' => fake()->paragraph(),
        ];
    }
}