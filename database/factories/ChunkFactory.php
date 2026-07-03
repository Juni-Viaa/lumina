<?php

namespace Database\Factories;

use App\Models\Chunk;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Chunk>
 */
class ChunkFactory extends Factory
{
    protected $model = Chunk::class;

    public function definition(): array
    {
        return [
            'document_id' => Upload::factory(),
            'chunk_text'  => fake()->paragraph(),
        ];
    }
}