<?php

namespace Database\Factories;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Upload>
 */
class UploadFactory extends Factory
{
    protected $model = Upload::class;

    public function definition(): array
    {
        $name = fake()->word() . '.pdf';

        return [
            'user_id'       => User::factory(),
            'document_name' => $name,
            'path_file'     => 'documents/1/' . $name,
            'file_type'     => 'pdf',
            'size'          => fake()->numberBetween(1000, 500000),
            'status'        => 'indexed',
        ];
    }

    public function processing(): static
    {
        return $this->state(fn () => ['status' => 'processing']);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed']);
    }
}