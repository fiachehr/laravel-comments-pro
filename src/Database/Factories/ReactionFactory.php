<?php

namespace Fiachehr\Comments\Database\Factories;

use Fiachehr\Comments\Enums\ReactionType;
use Fiachehr\Comments\Models\Reaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Fiachehr\Comments\Models\Reaction>
 */
class ReactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'comment_id' => $this->faker->numberBetween(1, 100),
            'user_id' => $this->faker->numberBetween(1, 10),
            'type' => $this->faker->randomElement([
                ReactionType::LIKE->value,
                ReactionType::DISLIKE->value,
            ]),
        ];
    }

    /**
     * Indicate that the reaction is a like.
     */
    public function like(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ReactionType::LIKE->value,
        ]);
    }

    /**
     * Indicate that the reaction is a dislike.
     */
    public function dislike(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ReactionType::DISLIKE->value,
        ]);
    }

    /**
     * Indicate that the reaction is from a guest user.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'guest_fingerprint' => $this->faker->uuid(),
        ]);
    }
}
