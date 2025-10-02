# Laravel Comments Pro

A comprehensive, feature-rich comments system for Laravel applications with support for nested comments, reactions, guest users, and advanced moderation features.

## 📋 Requirements

### PHP Version
- **PHP >= 8.1** (Required)
- **PHP 8.1, 8.2, 8.3, 8.4** (Supported)

### Laravel Version
- **Laravel 10.x** (LTS - Long Term Support)
- **Laravel 11.x** (Latest)

### Compatibility Matrix

| PHP Version | Laravel 10.x | Laravel 11.x |
| ----------- | ------------ | ------------ |
| 8.1         | ✅ Supported  | ✅ Supported  |
| 8.2         | ✅ Supported  | ✅ Supported  |
| 8.3         | ✅ Supported  | ✅ Supported  |
| 8.4         | ✅ Supported  | ✅ Supported  |

### Framework Dependency

⚠️ **This package is Laravel-specific** and requires:
- **Eloquent ORM** - Database models and relationships
- **Laravel Facades** - Service access layer
- **Laravel Service Providers** - Package registration
- **Laravel Events** - Event system integration
- **Laravel Migrations** - Database schema management
- **Laravel Artisan** - Command-line interface

**Not compatible with other frameworks** (Symfony, CodeIgniter, etc.)

## ✨ Features

- **Nested Comments** - Unlimited depth with configurable limits
- **Reactions System** - Like/Dislike functionality with statistics
- **Guest Support** - Allow anonymous users to comment
- **Moderation Tools** - Approve/reject comments with status management
- **Event System** - Fire events for comment creation, approval, and reactions
- **Tree Structure** - Convert flat comments to hierarchical tree
- **Guest Fingerprinting** - Track guest users without authentication
- **Bulk Operations** - Handle multiple comments and reactions efficiently
- **Popular Comments** - Get trending comments based on reactions
- **Soft Deletes** - Safe comment deletion with recovery options
- **Factory Support** - Built-in factories for testing
- **Comprehensive Testing** - Full test suite included

## 📦 Installation

### Prerequisites

Before installing, ensure you have:
- **PHP >= 8.1**
- **Laravel >= 10.0**
- **Composer** installed

### 1. Install via Composer

```bash
composer require fiachehr/laravel-comments-pro
```

> **Note:** This package requires Laravel framework and is not compatible with other PHP frameworks.

### 2. Publish and Run Migrations

```bash
php artisan vendor:publish --provider="Fiachehr\Comments\CommentsServiceProvider" --tag=comments-migrations
php artisan migrate
```

### 3. Publish Configuration

```bash
php artisan vendor:publish --provider="Fiachehr\Comments\CommentsServiceProvider" --tag=comments-config
```

### 4. (Optional) Publish Stubs

```bash
# Publish request stubs (recommended)
php artisan vendor:publish --provider="Fiachehr\Comments\CommentsServiceProvider" --tag=comments-requests

# Optional: Publish controller and route stubs
php artisan vendor:publish --provider="Fiachehr\Comments\CommentsServiceProvider" --tag=comments-controllers
php artisan vendor:publish --provider="Fiachehr\Comments\CommentsServiceProvider" --tag=comments-routes
```

## 🚀 Quick Start

### 1. Add Trait to Any Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Fiachehr\Comments\Traits\HasComments;

// Works with any model - Post, Article, Product, etc.
class Post extends Model
{
    use HasComments;
    
    // Your model code...
}

// Example with different models
class Article extends Model
{
    use HasComments;
}

class Product extends Model
{
    use HasComments;
}
```

### 2. Create Comments

```php
use Fiachehr\Comments\Facades\Comments;

// Works with any model that uses HasComments trait
$post = Post::find(1);
$article = Article::find(1);
$product = Product::find(1);

// For authenticated users
$comment = Comments::create([
    'body' => 'This is a great post!',
], $post);

// For guest users
$comment = Comments::create([
    'body' => 'Nice article!',
    'guest_name' => 'John Doe',
    'guest_email' => 'john@example.com',
], $article);

// Nested comments
$reply = Comments::create([
    'body' => 'Thanks for the comment!',
    'parent_id' => $comment->id,
], $product);
```

### 3. Approve Comments

```php
use Fiachehr\Comments\Facades\Comments;

$comment = Comments::approve($comment);
```

### 4. Add Reactions

```php
use Fiachehr\Comments\Facades\Reactions;
use Fiachehr\Comments\Enums\ReactionType;

// Like a comment
$reaction = Reactions::toggle($comment, ReactionType::LIKE);

// Dislike a comment
$reaction = Reactions::toggle($comment, ReactionType::DISLIKE);
```

### 5. Get Comments as Tree

```php
use Fiachehr\Comments\Facades\Comments;

$comments = $post->comments()->approved()->get();
$tree = Comments::toTree($comments);
```

## ⚙️ Configuration

### Comments Configuration

```php
// config/comments.php

return [
    'max_depth' => 5,
    'auto_approve_authenticated' => false,
    'reply_only_to_approved_parent' => true,
    
    'guests' => [
        'allowed' => true,
        'require_email' => true,
    ],
    
    'recaptcha' => [
        'enabled' => false,
        'secret' => env('RECAPTCHA_SECRET'),
        'version' => 'v3',
        'score' => 0.5,
    ],
];
```

### Model Configuration

```php
// In your model that uses HasComments trait

class Post extends Model
{
    use HasComments;
    
    // Optional: Override default comment settings
    public function getCommentSettings(): array
    {
        return [
            'max_depth' => 3,
            'auto_approve' => true,
        ];
    }
}
```

## 🔧 Advanced Usage

### Service Classes

```php
use Fiachehr\Comments\Services\CommentsService;
use Fiachehr\Comments\Services\ReactionService;

// Direct service usage
$commentsService = app(CommentsService::class);
$comment = $commentsService->createComment($data, $post);

$reactionService = app(ReactionService::class);
$reaction = $reactionService->toggleReaction($comment, ReactionType::LIKE);
```

### Events

```php
use Fiachehr\Comments\Events\CommentCreated;
use Fiachehr\Comments\Events\CommentApproved;
use Fiachehr\Comments\Events\ReactionToggled;

// Listen to events
Event::listen(CommentCreated::class, function (CommentCreated $event) {
    // Handle new comment
    $comment = $event->comment;
    // Send notification, log activity, etc.
});
```

### Scopes and Relationships

```php
// Get approved comments
$approvedComments = $post->comments()->approved()->get();

// Get comments with reactions
$commentsWithReactions = $post->comments()->withReactions()->get();

// Get popular comments
$popularComments = app(ReactionService::class)->getPopularComments(10, '7 days');
```

### Bulk Operations

```php
use Fiachehr\Comments\Facades\Reactions;

// Bulk reactions
$results = Reactions::bulkToggle([1, 2, 3], ReactionType::LIKE);

// Get reaction statistics
$stats = Reactions::getStats($comment);
// Returns: ['likes' => 5, 'dislikes' => 2, 'total' => 7]
```

## 🎯 API Examples

### Controller Implementation

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use Fiachehr\Comments\Facades\Comments;
use Fiachehr\Comments\Facades\Reactions;
use Fiachehr\Comments\Enums\ReactionType;
use Illuminate\Database\Eloquent\Model;

class CommentController extends Controller
{
    // Works with any model that uses HasComments trait
    public function store(StoreCommentRequest $request, Model $commentable)
    {
        $comment = Comments::create($request->validated(), $commentable);
        
        return response()->json([
            'success' => true,
            'comment' => $comment,
        ]);
    }
    
    public function approve(Comment $comment)
    {
        $approvedComment = Comments::approve($comment);
        
        return response()->json([
            'success' => true,
            'comment' => $approvedComment,
        ]);
    }
    
    public function react(Comment $comment, string $type)
    {
        $reactionType = ReactionType::from($type);
        $reaction = Reactions::toggle($comment, $reactionType);
        
        return response()->json([
            'success' => true,
            'reaction' => $reaction,
        ]);
    }
    
    // Works with any commentable model
    public function tree(Model $commentable)
    {
        $comments = $commentable->comments()->approved()->get();
        $tree = Comments::toTree($comments);
        
        return response()->json($tree);
    }
}
```

### Request Validation

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Fiachehr\Comments\Rules\GuestFingerprint;

class StoreCommentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'body' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
            'guest_name' => 'required_if:user_id,null|string|max:255',
            'guest_email' => 'required_if:user_id,null|email|max:255',
            'guest_fingerprint' => ['nullable', new GuestFingerprint()],
        ];
    }
}
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test tests/Unit/ --filter="Comments"

# Run specific test suites
php artisan test tests/Unit/BasicCommentsTest.php
php artisan test tests/Unit/CommentsServiceTest.php
php artisan test tests/Unit/SimpleCommentsTest.php
```

### Test Examples

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use Fiachehr\Comments\Facades\Comments;
use Fiachehr\Comments\Facades\Reactions;
use Fiachehr\Comments\Enums\ReactionType;

class CommentTest extends TestCase
{
    public function test_can_create_comment()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        $this->actingAs($user);
        
        $comment = Comments::create([
            'body' => 'Test comment',
        ], $post);
        
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('Test comment', $comment->body);
    }
    
    public function test_can_add_reaction()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comments::create(['body' => 'Test'], $post);
        
        $this->actingAs($user);
        
        $reaction = Reactions::toggle($comment, ReactionType::LIKE);
        
        $this->assertInstanceOf(Reaction::class, $reaction);
        $this->assertEquals('like', $reaction->type);
    }
}
```

## 📊 Database Schema

### Comments Table

```sql
CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commentable_type VARCHAR(255) NOT NULL,
    commentable_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    guest_name VARCHAR(255) NULL,
    guest_email VARCHAR(255) NULL,
    guest_ip VARCHAR(45) NULL,
    body TEXT NOT NULL,
    parent_id BIGINT UNSIGNED NULL,
    status ENUM('pending', 'approved', 'spam') DEFAULT 'pending',
    depth TINYINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_commentable (commentable_type, commentable_id),
    INDEX idx_user (user_id),
    INDEX idx_parent (parent_id),
    INDEX idx_status (status),
    INDEX idx_depth (depth)
);
```

### Reactions Table

```sql
CREATE TABLE reactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    comment_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    guest_fingerprint VARCHAR(255) NULL,
    type ENUM('like', 'dislike') NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY unique_user_reaction (comment_id, user_id),
    UNIQUE KEY unique_guest_reaction (comment_id, guest_fingerprint),
    INDEX idx_comment (comment_id),
    INDEX idx_user (user_id),
    INDEX idx_type (type)
);
```

## 🔒 Security Features

- **CSRF Protection** - Built-in CSRF token validation
- **Rate Limiting** - Configurable rate limits for comments
- **Spam Protection** - Optional reCAPTCHA integration
- **Guest Fingerprinting** - Secure guest user tracking
- **Input Validation** - Comprehensive validation rules
- **SQL Injection Protection** - Eloquent ORM protection

## 🎨 Frontend Integration

### Vue.js Example

```javascript
// CommentComponent.vue
<template>
  <div class="comments">
    <div v-for="comment in comments" :key="comment.id" class="comment">
      <div class="comment-body">{{ comment.body }}</div>
      <div class="comment-actions">
        <button @click="toggleReaction(comment, 'like')">
          👍 {{ comment.likes }}
        </button>
        <button @click="toggleReaction(comment, 'dislike')">
          👎 {{ comment.dislikes }}
        </button>
      </div>
      <div v-if="comment.children" class="replies">
        <CommentComponent :comments="comment.children" />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['comments'],
  methods: {
    async toggleReaction(comment, type) {
      try {
        const response = await axios.post(`/comments/${comment.id}/react`, {
          type: type
        });
        // Update UI
      } catch (error) {
        console.error('Failed to toggle reaction:', error);
      }
    }
  }
}
</script>
```

### React Example

```jsx
// CommentComponent.jsx
import React, { useState } from 'react';
import axios from 'axios';

const CommentComponent = ({ comments }) => {
  const [reactions, setReactions] = useState({});

  const toggleReaction = async (commentId, type) => {
    try {
      const response = await axios.post(`/comments/${commentId}/react`, {
        type: type
      });
      setReactions(prev => ({
        ...prev,
        [commentId]: response.data.reaction
      }));
    } catch (error) {
      console.error('Failed to toggle reaction:', error);
    }
  };

  return (
    <div className="comments">
      {comments.map(comment => (
        <div key={comment.id} className="comment">
          <div className="comment-body">{comment.body}</div>
          <div className="comment-actions">
            <button onClick={() => toggleReaction(comment.id, 'like')}>
              👍 {comment.likes}
            </button>
            <button onClick={() => toggleReaction(comment.id, 'dislike')}>
              👎 {comment.dislikes}
            </button>
          </div>
          {comment.children && (
            <CommentComponent comments={comment.children} />
          )}
        </div>
      ))}
    </div>
  );
};

export default CommentComponent;
```

## 🚀 Performance Optimization

### Database Indexing

```php
// Add custom indexes for better performance
Schema::table('comments', function (Blueprint $table) {
    $table->index(['commentable_type', 'commentable_id', 'status']);
    $table->index(['parent_id', 'depth']);
    $table->index(['created_at', 'status']);
});
```

### Caching

```php
use Illuminate\Support\Facades\Cache;

// Cache popular comments
$popularComments = Cache::remember('popular_comments', 3600, function () {
    return app(ReactionService::class)->getPopularComments(10);
});

// Cache comment trees
$commentTree = Cache::remember("comments_tree_{$post->id}", 1800, function () use ($post) {
    return Comments::toTree($post->comments()->approved()->get());
});
```

### Eager Loading

```php
// Load comments with relationships
$comments = $post->comments()
    ->with(['user', 'reactions', 'children'])
    ->approved()
    ->get();
```

## 🔧 Troubleshooting

### Common Issues

1. **Compatibility Issues**
   ```bash
   # Check PHP version
   php --version
   
   # Check Laravel version
   php artisan --version
   
   # Ensure minimum requirements are met
   # PHP >= 8.1, Laravel >= 10.0
   ```

2. **Migration Errors**
   ```bash
   # Clear cache and re-run migrations
   php artisan cache:clear
   php artisan config:clear
   php artisan migrate:fresh
   ```

3. **Factory Not Found**
   ```php
   // Make sure to publish factories
   php artisan vendor:publish --provider="Fiachehr\Comments\CommentsServiceProvider" --tag=comments-factories
   ```

4. **Event Not Firing**
   ```php
   // Check if events are registered in EventServiceProvider
   protected $listen = [
       CommentCreated::class => [
           // Your listeners
       ],
   ];
   ```

5. **Framework Compatibility**
   ```php
   // This package only works with Laravel
   // Not compatible with: Symfony, CodeIgniter, etc.
   // Requires: Laravel 10+ with PHP 8.1+
   ```

### Debug Mode

```php
// Enable debug logging
Log::info('Comment created', ['comment' => $comment->toArray()]);
Log::info('Reaction toggled', ['reaction' => $reaction->toArray()]);
```

## 📈 Monitoring

### Analytics

```php
// Track comment metrics
$stats = [
    'total_comments' => Comment::count(),
    'approved_comments' => Comment::approved()->count(),
    'pending_comments' => Comment::where('status', 'pending')->count(),
    'total_reactions' => Reaction::count(),
    'popular_posts' => app(ReactionService::class)->getPopularComments(5),
];
```

### Health Checks

```php
// Check system health
$health = [
    'database_connection' => DB::connection()->getPdo() !== null,
    'migrations_up_to_date' => !Artisan::call('migrate:status'),
    'config_loaded' => config('comments') !== null,
    'services_registered' => app()->bound(CommentsService::class),
];
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

- **Documentation**: [GitHub Wiki](https://github.com/fiachehr/laravel-comments-pro/wiki)
- **Issues**: [GitHub Issues](https://github.com/fiachehr/laravel-comments-pro/issues)
- **Discussions**: [GitHub Discussions](https://github.com/fiachehr/laravel-comments-pro/discussions)

## 🙏 Acknowledgments

- Laravel Framework
- Eloquent ORM
- PHP Community
- All contributors and testers

---

**Made with ❤️ by [Fiachehr](https://github.com/fiachehr)**
