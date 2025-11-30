<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('github_issues', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('assignee_id')->nullable()->index();
            $table->unsignedBigInteger('closed_by_id')->nullable()->index();
            $table->integer('number');
            $table->string('state');
            $table->boolean('locked');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('author_association');
            $table->string('active_lock_reason')->nullable();
            $table->json('reactions')->nullable();
            $table->boolean('performed_via_github_app')->nullable();
            $table->string('state_reason')->nullable();
            $table->dateTimeTz('closed_at')->nullable();
            $table->dateTimeTz('created_at');
            $table->dateTimeTz('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('github_issues');
    }
};
