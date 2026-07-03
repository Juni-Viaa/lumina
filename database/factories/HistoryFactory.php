<?php

namespace Database\Factories;

use App\Models\History;
use App\Models\User;
use App\Models\QueryLog;
use App\Models\Answer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<History>
 */
class HistoryFactory extends Factory
{
    protected $model = History::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'query_id'  => QueryLog::factory(),
            'answer_id' => Answer::factory(),
        ];
    }
}