<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

it('creates compound indexes for common comment queries', function (): void {
    $commentIndexes = sqliteIndexNames('comments');
    $reactionIndexes = sqliteIndexNames('reactions');

    expect($commentIndexes)
        ->toContain('commentable_approved_created_index')
        ->toContain('comments_reply_approved_created_index')
        ->and($reactionIndexes)
        ->toContain('reactions_comment_type_index');
});

it('orders compound index columns for pagination and counts', function (): void {
    expect(sqliteIndexColumns('commentable_approved_created_index'))
        ->toBe(['commentable_type', 'commentable_id', 'approved', 'created_at'])
        ->and(sqliteIndexColumns('comments_reply_approved_created_index'))
        ->toBe(['reply_id', 'approved', 'created_at'])
        ->and(sqliteIndexColumns('reactions_comment_type_index'))
        ->toBe(['comment_id', 'type']);
});

/**
 * @return list<string>
 */
function sqliteIndexNames(string $table): array
{
    return collect(DB::select("pragma index_list('{$table}')"))
        ->pluck('name')
        ->values()
        ->all();
}

/**
 * @return list<string>
 */
function sqliteIndexColumns(string $index): array
{
    return collect(DB::select("pragma index_info('{$index}')"))
        ->pluck('name')
        ->values()
        ->all();
}
