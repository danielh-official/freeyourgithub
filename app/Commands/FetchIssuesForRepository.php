<?php

namespace App\Commands;

use App\Models\GitHubIssue;
use App\Models\GitHubLabel;
use App\Models\GitHubUser;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;

class FetchIssuesForRepository extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-issues-for-repository {url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch issues for a given repository.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (empty(config('github.token'))) {
            $this->error('GitHub token is not set in the configuration.');

            return 1;
        }

        $url = $this->argument('url');

        $paths = trim(parse_url($url, PHP_URL_PATH), '/');

        $repository = explode('/', $paths)[1];
        $username = explode('/', $paths)[0];

        $url = "https://api.github.com/repos/{$username}/{$repository}/issues";

        $response = Http::withHeader('Accept', 'application/vnd.github+json')
            ->withHeader('Authorization', 'Bearer '.config('github.token'))
            ->withHeader('X-GitHub-Api-Version', '2022-11-28')
            ->get($url)
            ->throw();

        $issuesJson = $response->json();

        DB::transaction(function () use ($issuesJson) {
            $usersToUpsert = collect();
            $labelsToUpsert = collect();
            $issuesToUpsert = collect();
            $issueLabelsToUpsert = collect();

            foreach ($issuesJson as $issue) {
                $user = $issue['user'] ?? null;

                $userId = $user['id'] ?? null;

                if (filled($user) && filled($userId)) {
                    $usersToUpsert->push([
                        'id' => $user['id'],
                        'login' => $user['login'],
                    ]);
                } else {
                    continue;
                }

                $assignee = $issue['assignee'] ?? null;

                if (filled($assignee) && filled($assignee['id'] ?? null)) {
                    $usersToUpsert->push([
                        'id' => $assignee['id'],
                        'login' => $assignee['login'],
                    ]);
                }

                $closedBy = $issue['closed_by'] ?? null;

                if (filled($closedBy) && filled($closedBy['id'] ?? null)) {
                    $usersToUpsert->push([
                        'id' => $closedBy['id'],
                        'login' => $closedBy['login'],
                    ]);
                }

                $labels = $issue['labels'] ?? [];

                foreach ($labels as $label) {
                    if (empty($label['id'] ?? null)) {
                        continue;
                    }

                    $labelsToUpsert->push([
                        'id' => $label['id'] ?? null,
                        'name' => $label['name'] ?? null,
                        'description' => $label['description'] ?? null,
                        'color' => $label['color'] ?? null,
                        'default' => $label['default'] ?? null,
                    ]);

                    $issueLabelsToUpsert->push([
                        'issue_id' => $issue['id'],
                        'label_id' => $label['id'],
                    ]);
                }

                $issuesToUpsert->push([
                    'id' => $issue['id'],
                    'user_id' => $userId,
                    'assignee_id' => $assignee['id'] ?? null,
                    'closed_by_id' => $closedBy['id'] ?? null,
                    'number' => $issue['number'],
                    'state' => $issue['state'],
                    'locked' => $issue['locked'],
                    'title' => $issue['title'],
                    'body' => $issue['body'] ?? null,
                    'author_association' => $issue['author_association'],
                    'active_lock_reason' => $issue['active_lock_reason'] ?? null,
                    'body' => $issue['body'] ?? null,
                    'reactions' => isset($issue['reactions']) ? json_encode($issue['reactions']) : null,
                    'performed_via_github_app' => $issue['performed_via_github_app'] ?? null,
                    'state_reason' => $issue['state_reason'] ?? null,
                    'closed_at' => $issue['closed_at'] ?? null,
                    'created_at' => $issue['created_at'],
                    'updated_at' => $issue['updated_at'],
                ]);
            }

            GitHubUser::upsert(
                $usersToUpsert->toArray(),
                uniqueBy: ['id'],
                update: ['login']
            );

            GitHubLabel::upsert(
                $labelsToUpsert->toArray(),
                uniqueBy: ['id'],
                update: ['name', 'description', 'color', 'default']
            );

            GitHubIssue::upsert(
                $issuesToUpsert->toArray(),
                uniqueBy: ['id'],
                update: [
                    'user_id',
                    'number',
                    'state',
                    'locked',
                    'title',
                    'body',
                    'author_association',
                    'active_lock_reason',
                    'body',
                    'reactions',
                    'performed_via_github_app',
                    'state_reason',
                    'closed_at',
                    'created_at',
                    'updated_at',
                ]
            );

            DB::table('github_issue_labels')->upsert(
                $issueLabelsToUpsert->toArray(),
                uniqueBy: ['issue_id', 'label_id']
            );
        });
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
