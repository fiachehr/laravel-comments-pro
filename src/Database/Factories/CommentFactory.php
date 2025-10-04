<?php

namespace Fiachehr\Comments\Database\Factories;

use Fiachehr\Comments\Enums\CommentStatusType;
use Fiachehr\Comments\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Fiachehr\Comments\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'commentable_type' => 'App\\Models\\Post',
            'commentable_id' => $this->faker->numberBetween(1, 100),
            'user_id' => $this->faker->numberBetween(1, 10),
            'body' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement([
                CommentStatusType::PENDING->value,
                CommentStatusType::APPROVED->value,
                CommentStatusType::SPAM->value,
            ]),
            'depth' => 0,
        ];
    }

    /**
     * Indicate that the comment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatusType::APPROVED->value,
        ]);
    }

    /**
     * Indicate that the comment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatusType::PENDING->value,
        ]);
    }

    /**
     * Indicate that the comment is spam.
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatusType::SPAM->value,
        ]);
    }

    /**
     * Indicate that the comment is a guest comment.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'guest_name' => $this->faker->name(),
            'guest_email' => $this->faker->safeEmail(),
            'guest_ip' => $this->faker->ipv4(),
        ]);
    }

    /**
     * Indicate that the comment is a reply to another comment.
     */
    public function reply(int $parentId, int $depth = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
            'depth' => $depth,
        ]);
    }
}
