<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'code' => fake()->unique()->slug(),
        ];
    }

    public function pizza(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Пиццы',
            'code' => 'pizza',
        ]);
    }

    public function drink(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Напитки',
            'code' => 'drink',
        ]);
    }
}
