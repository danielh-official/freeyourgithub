<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitHubIssue extends Model
{
    /** @use HasFactory<\Database\Factories\GitHubIssueFactory> */
    use HasFactory;

    protected $table = 'github_issues';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
      'reactions' => 'json',
      'closed_at' => 'datetime',
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
    ];
}
