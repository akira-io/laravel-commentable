<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $commentTable = $this->commentTable();
        $reactionTable = $this->reactionTable();
        $revisionTable = $this->revisionTable();

        Schema::create($commentTable, function (Blueprint $table) use ($commentTable): void {
            $table->id();

            $table->nullableMorphs('commentable');
            $table->nullableMorphs('commenter');
            $table->unsignedBigInteger('reply_id')->nullable()->index();
            $table->longText('content');
            $table->boolean('approved')->default(false)->index();
            $table->foreign('reply_id')->references('id')->on($commentTable)->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['commentable_type', 'commentable_id', 'approved', 'created_at'], 'commentable_approved_created_index');
            $table->index(['reply_id', 'approved', 'created_at'], 'comments_reply_approved_created_index');
        });

        Schema::create($revisionTable, function (Blueprint $table) use ($commentTable): void {
            $table->id();
            $table->foreignId('comment_id')->constrained($commentTable)->cascadeOnDelete();
            $table->nullableMorphs('editor');
            $table->longText('previous_content');
            $table->longText('new_content');
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create($reactionTable, function (Blueprint $table) use ($commentTable): void {
            $table->id();
            $table->morphs('owner');
            $table->foreignId('comment_id')->constrained($commentTable)->cascadeOnDelete();
            $table->string('type');
            $table->timestamps();
            $table->index(['comment_id', 'type'], 'reactions_comment_type_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->reactionTable());
        Schema::dropIfExists($this->revisionTable());
        Schema::dropIfExists($this->commentTable());
    }

    private function commentTable(): string
    {
        $table = config('commentable.comment_table', 'comments');

        return is_string($table) ? $table : 'comments';
    }

    private function reactionTable(): string
    {
        $table = config('commentable.reaction_table', 'reactions');

        return is_string($table) ? $table : 'reactions';
    }

    private function revisionTable(): string
    {
        $table = config('commentable.revision_table', 'comment_revisions');

        return is_string($table) ? $table : 'comment_revisions';
    }
};
