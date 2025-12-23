# Roadmap

This document outlines potential future enhancements and extension points for Laravel Commentable, based on the current architecture and existing abstractions.

## Planned Features

### Comment Moderation System

- Admin dashboard integration for reviewing pending comments
- Bulk approval/rejection workflows
- Flagging system for inappropriate content
- Custom moderation policies per commentable model

### Notification System

- Event-based notifications when comments are created
- Reply notifications for comment authors
- Mention notifications (@username)
- Email and database notification channels
- Customizable notification templates

### Rich Content Support

- Markdown rendering for comment content
- HTML sanitization and XSS protection
- File attachment support
- Image embedding
- Link previews

### Advanced Reaction System

- Configurable reaction types (like, love, laugh, etc.)
- Reaction analytics and statistics
- Reaction grouping by type
- User-specific reaction history

### Thread Management

- Comment sorting (newest, oldest, popular)
- Nested reply depth limits
- Thread collapsing/expanding
- Best answer marking for Q&A scenarios

### Performance Optimization

- Eager loading helpers for comments and replies
- Query scopes for common filtering patterns
- Caching strategies for comment counts
- Pagination utilities for large comment threads

### Permission System

- Integration with Laravel authorization
- Granular permissions (edit own comments, delete any comment)
- Role-based comment moderation
- Custom policy support per model

### Analytics and Reporting

- Comment engagement metrics
- Top commenters leaderboard
- Activity timeline
- Export functionality for moderation reports

### API Enhancements

- Soft delete support with recovery options
- Comment editing with revision history
- Comment pinning and highlighting
- Vote-based comment ranking

### Developer Experience

- Blade components for common UI patterns
- Vue/React components (optional)
- API resource transformers
- Webhook support for external integrations

**Next:** [Installation](01-installation.md)
