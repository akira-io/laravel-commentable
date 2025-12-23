# Examples

Real-world implementation examples for common use cases.

## Blog Comment System

Complete implementation for a blog with posts, comments, and replies.

### Models

```php
<?php

namespace App\Models;

use Akira\Commentable\Concerns\Commentable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use Commentable;

    protected $fillable = ['title', 'content', 'author_id'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
```

```php
<?php

namespace App\Models;

use Akira\Commentable\Concerns\Commenter;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Commenter;

    protected $fillable = ['name', 'email', 'password'];
}
```

### Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Akira\Commentable\Models\Comment;
use Akira\Commentable\Exceptions\DeleteCommentNotAllowedException;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $comment = $request->user()->comment($post, $request->content);

        return response()->json([
            'message' => 'Comment created successfully',
            'comment' => $comment->load('commenter'),
        ], 201);
    }

    public function reply(Request $request, Comment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $reply = $request->user()->reply($comment, $request->content);

        return response()->json([
            'message' => 'Reply created successfully',
            'reply' => $reply->load('commenter'),
        ], 201);
    }

    public function destroy(Request $request, Comment $comment)
    {
        try {
            $request->user()->deleteComment($comment);

            return response()->json([
                'message' => 'Comment deleted successfully',
            ]);
        } catch (DeleteCommentNotAllowedException $e) {
            return response()->json([
                'message' => 'Unauthorized to delete this comment',
            ], 403);
        }
    }

    public function index(Post $post)
    {
        $comments = $post->comments()
            ->where('approved', true)
            ->with(['commenter', 'replies.commenter', 'reactions'])
            ->latest()
            ->paginate(20);

        return response()->json($comments);
    }
}
```

### Routes

```php
use App\Http\Controllers\CommentController;

Route::middleware(['auth'])->group(function () {
    Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
    Route::post('/comments/{comment}/replies', [CommentController::class, 'reply']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});

Route::get('/posts/{post}/comments', [CommentController::class, 'index']);
```

---

## Comment Moderation System

Admin panel for approving and managing comments.

### Admin Controller

```php
<?php

namespace App\Http\Controllers\Admin;

use Akira\Commentable\Models\Comment;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:moderate-comments');
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $comments = Comment::query()
            ->with(['commenter', 'commentable'])
            ->when($status === 'pending', fn($q) => $q->where('approved', false))
            ->when($status === 'approved', fn($q) => $q->where('approved', true))
            ->latest()
            ->paginate(50);

        return view('admin.moderation.index', compact('comments'));
    }

    public function approve(Comment $comment)
    {
        $comment->update(['approved' => true]);

        return back()->with('success', 'Comment approved successfully');
    }

    public function reject(Comment $comment)
    {
        $comment->delete();

        return back()->with('success', 'Comment rejected and deleted');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:comments,id',
        ]);

        Comment::whereIn('id', $request->comment_ids)
            ->update(['approved' => true]);

        return back()->with('success', 'Comments approved successfully');
    }
}
```

### Blade View

```blade
{{-- resources/views/admin/moderation/index.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="container">
    <h1>Comment Moderation</h1>

    <div class="filters mb-4">
        <a href="{{ route('admin.moderation.index', ['status' => 'pending']) }}" 
           class="btn btn-secondary">
            Pending
        </a>
        <a href="{{ route('admin.moderation.index', ['status' => 'approved']) }}" 
           class="btn btn-secondary">
            Approved
        </a>
    </div>

    @foreach($comments as $comment)
    <div class="card mb-3">
        <div class="card-body">
            <p><strong>{{ $comment->commenter->name }}</strong></p>
            <p>{{ $comment->content }}</p>
            <p class="text-muted">
                On: {{ $comment->commentable?->title ?? 'N/A' }}
            </p>

            @if(!$comment->approved)
            <form action="{{ route('admin.moderation.approve', $comment) }}" 
                  method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success">Approve</button>
            </form>

            <form action="{{ route('admin.moderation.reject', $comment) }}" 
                  method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">Reject</button>
            </form>
            @endif
        </div>
    </div>
    @endforeach

    {{ $comments->links() }}
</div>
@endsection
```

---

## Reaction System

Implement likes and other reactions.

### Reaction Controller

```php
<?php

namespace App\Http\Controllers;

use Akira\Commentable\Models\Comment;
use Akira\Commentable\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function store(Request $request, Comment $comment)
    {
        $request->validate([
            'type' => 'required|in:like,love,laugh,sad,angry',
        ]);

        // Remove existing reaction by this user
        Reaction::where('comment_id', $comment->id)
            ->where('owner_type', User::class)
            ->where('owner_id', $request->user()->id)
            ->delete();

        // Create new reaction
        $reaction = Reaction::create([
            'comment_id' => $comment->id,
            'owner_type' => User::class,
            'owner_id' => $request->user()->id,
            'type' => $request->type,
        ]);

        return response()->json([
            'message' => 'Reaction added',
            'reaction' => $reaction,
            'counts' => $this->getReactionCounts($comment),
        ]);
    }

    public function destroy(Request $request, Comment $comment)
    {
        Reaction::where('comment_id', $comment->id)
            ->where('owner_type', User::class)
            ->where('owner_id', $request->user()->id)
            ->delete();

        return response()->json([
            'message' => 'Reaction removed',
            'counts' => $this->getReactionCounts($comment),
        ]);
    }

    private function getReactionCounts(Comment $comment): array
    {
        return $comment->reactions()
            ->select('type', \DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }
}
```

### Vue Component

```vue
<template>
  <div class="reactions">
    <button 
      v-for="type in reactionTypes" 
      :key="type"
      @click="toggleReaction(type)"
      :class="{ active: userReaction === type }"
      class="reaction-btn"
    >
      {{ getEmoji(type) }}
      <span v-if="counts[type]">{{ counts[type] }}</span>
    </button>
  </div>
</template>

<script>
export default {
  props: {
    commentId: Number,
    initialReactions: Object,
    initialUserReaction: String,
  },

  data() {
    return {
      reactionTypes: ['like', 'love', 'laugh', 'sad', 'angry'],
      counts: { ...this.initialReactions },
      userReaction: this.initialUserReaction,
    };
  },

  methods: {
    async toggleReaction(type) {
      if (this.userReaction === type) {
        await this.removeReaction();
      } else {
        await this.addReaction(type);
      }
    },

    async addReaction(type) {
      const response = await fetch(`/comments/${this.commentId}/reactions`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ type }),
      });

      const data = await response.json();
      this.counts = data.counts;
      this.userReaction = type;
    },

    async removeReaction() {
      const response = await fetch(`/comments/${this.commentId}/reactions`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
      });

      const data = await response.json();
      this.counts = data.counts;
      this.userReaction = null;
    },

    getEmoji(type) {
      const emojis = {
        like: '👍',
        love: '❤️',
        laugh: '😂',
        sad: '😢',
        angry: '😡',
      };
      return emojis[type];
    },
  },
};
</script>
```

---

## Custom Authorization

Allow post authors to delete any comment on their posts.

### Custom Commenter Implementation

```php
<?php

namespace App\Models;

use Akira\Commentable\Concerns\Commenter;
use Akira\Commentable\Contracts\CommentContract;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Commenter;

    public function approveCommentDeletion(CommentContract $comment): bool
    {
        // Admins can delete any comment
        if ($this->is_admin) {
            return true;
        }

        // Users can delete their own comments
        if ($this->id === $comment->commenter_id) {
            return true;
        }

        // Post authors can delete comments on their posts
        if ($comment->commentable_type === Post::class) {
            $post = $comment->commentable;
            if ($post && $post->author_id === $this->id) {
                return true;
            }
        }

        return false;
    }
}
```

---

## API Resource Transformation

Format comment data for API responses.

### Comment Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'approved' => $this->approved,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'is_edited' => $this->created_at->ne($this->updated_at),
            
            'commenter' => [
                'id' => $this->commenter->id,
                'name' => $this->commenter->name,
                'avatar' => $this->commenter->avatar_url ?? null,
            ],

            'replies_count' => $this->replies()->count(),
            'replies' => ReplyResource::collection($this->whenLoaded('replies')),

            'reactions' => $this->reactions()
                ->select('type', \DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type'),

            'user_reaction' => $this->reactions()
                ->where('owner_id', $request->user()?->id)
                ->where('owner_type', User::class)
                ->value('type'),
        ];
    }
}
```

**Previous:** [Testing](07-testing.md) | **Next:** [Troubleshooting](09-troubleshooting.md)
