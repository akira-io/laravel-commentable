# Security Policy

## Supported Versions

We release patches for security vulnerabilities in the following versions:

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

The Laravel Commentable team takes security vulnerabilities seriously. We appreciate your efforts to responsibly disclose your findings.

### How to Report

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report them via email to:

**kidiatoliny@akira-io.com**

### What to Include

To help us better understand and resolve the issue, please include as much of the following information as possible:

- **Type of vulnerability** (e.g., SQL injection, XSS, authentication bypass)
- **Full paths of source file(s)** related to the vulnerability
- **Location of the affected source code** (tag/branch/commit or direct URL)
- **Step-by-step instructions** to reproduce the issue
- **Proof-of-concept or exploit code** (if possible)
- **Impact of the vulnerability** and how it might be exploited
- **Your name/handle** for attribution (if you wish to be credited)

### What to Expect

After you submit a report, you can expect:

1. **Acknowledgment** - We will acknowledge receipt of your vulnerability report within 48 hours
2. **Assessment** - We will assess the vulnerability and determine its severity
3. **Updates** - We will keep you informed about our progress
4. **Resolution** - We will develop and test a fix
5. **Release** - We will release a security patch
6. **Disclosure** - We will publicly disclose the vulnerability after the patch is released

### Timeline

- **Initial Response**: Within 48 hours
- **Status Update**: Within 7 days
- **Fix Development**: Depends on severity and complexity
- **Public Disclosure**: After patch release, typically 30 days from initial report

## Security Best Practices

When using Laravel Commentable in your application:

### Input Validation

Always validate and sanitize user input before storing comments:

```php
$request->validate([
    'content' => 'required|string|max:5000',
]);

$comment = $user->comment($post, strip_tags($request->content));
```

### XSS Prevention

Escape output when displaying comments in views:

```blade
{{ $comment->content }}  {{-- Blade automatically escapes --}}
```

If you need to allow HTML, use a sanitization library:

```php
use HTMLPurifier;

$clean = HTMLPurifier::clean($comment->content);
```

### Authorization

Always verify user authorization before operations:

```php
// Check before deleting
if (!$user->approveCommentDeletion($comment)) {
    abort(403, 'Unauthorized');
}

$user->deleteComment($comment);
```

### SQL Injection Protection

Laravel Commentable uses Eloquent ORM, which provides protection against SQL injection. However:

- Never use raw queries with user input
- Always use parameter binding if raw queries are necessary
- Use query scopes and relationship methods

### Mass Assignment Protection

The package uses `$fillable` properties to protect against mass assignment vulnerabilities. When extending models:

```php
class Comment extends BaseComment
{
    protected $fillable = [
        'content',
        'commenter_type',
        'commenter_id',
        'approved',
    ];
}
```

### Rate Limiting

Implement rate limiting for comment creation to prevent spam:

```php
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
    ->middleware('throttle:10,1'); // 10 requests per minute
```

### Content Moderation

Use the approval system to moderate content:

```php
// Only show approved comments to non-admins
$comments = $post->comments()
    ->where('approved', true)
    ->get();
```

## Known Security Considerations

### User-Generated Content

Comments contain user-generated content. Implement appropriate measures:

- Content filtering for profanity or spam
- Markdown/HTML sanitization
- File upload restrictions (if adding attachments)
- Link validation to prevent phishing

### Polymorphic Relationships

The package uses polymorphic relationships. Ensure:

- Type validation before relationship operations
- Access control for different entity types
- Proper authorization checks across models

### Cascade Deletions

Comments are deleted when their parent is deleted. Consider:

- Soft deletes if you need comment history
- Backup strategies for important data
- Notification of users when their comments are removed

## Security Updates

Security updates will be released as soon as possible after a vulnerability is confirmed. To stay informed:

- Watch the [GitHub repository](https://github.com/kidiatoliny/laravel-commentable)
- Subscribe to release notifications
- Follow [@akira-io](https://github.com/kidiatoliny) on GitHub

## Recognition

We appreciate security researchers who help keep Laravel Commentable secure. With your permission, we will acknowledge your contribution in:

- The CHANGELOG.md file
- The security advisory
- This SECURITY.md file

## Questions

If you have questions about this security policy, please contact:

**kidiatoliny@akira-io.com**

Thank you for helping keep Laravel Commentable and its users safe!
