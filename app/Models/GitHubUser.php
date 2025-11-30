<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitHubUser extends Model
{
    /** @use HasFactory<\Database\Factories\GitHubUserFactory> */
    use HasFactory;

    protected $table = 'github_users';

    protected $guarded = [];

    public $timestamps = false;
}
