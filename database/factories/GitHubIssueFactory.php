<?php

namespace Database\Factories;

use App\Models\GitHubUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GitHubIssue>
 */
class GitHubIssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user = GitHubUser::factory();

        return [
            'id' => $this->faker->unique()->numberBetween(1, 100000),
            'user_id' => $user,
            'assignee_id' => $user,
            'closed_by_id' => null,
            'number' => $this->faker->numberBetween(1, 10000),
            'state' => $this->faker->randomElement(['open', 'closed']),
            'locked' => $this->faker->boolean(),
            'title' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now', 'UTC'),
            'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now', 'UTC'),
            'closed_at' => null,
            'author_association' => $this->faker->randomElement(['OWNER', 'CONTRIBUTOR', 'MEMBER', 'NONE']),
            'active_lock_reason' => null,
            'reactions' => null,
            'performed_via_github_app' => null,
            'state_reason' => null,
        ];
    }
}
