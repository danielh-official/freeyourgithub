<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GitHubLabel extends Model
{
    /** @use HasFactory<\Database\Factories\GitHubLabelFactory> */
    use HasFactory;

    protected $table = 'github_labels';

    protected $guarded = [];

    public $timestamps = false;
}
