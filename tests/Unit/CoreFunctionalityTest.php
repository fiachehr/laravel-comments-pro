<?php

namespace Fiachehr\Comments\Tests;

use Fiachehr\Comments\Enums\CommentStatusType;
use Fiachehr\Comments\Enums\ReactionType;
use Fiachehr\Comments\Helper\GuestFingerprint;
use Fiachehr\Comments\Models\Comment;
use Fiachehr\Comments\Models\Reaction;
use PHPUnit\Framework\TestCase;

class CoreFunctionalityTest extends TestCase
{
    public function test_comment_model_can_be_instantiated()
    {
        $comment = new Comment;
        $this->assertInstanceOf(Comment::class, $comment);
    }

    public function test_reaction_model_can_be_instantiated()
    {
        $reaction = new Reaction;
        $this->assertInstanceOf(Reaction::class, $reaction);
    }

    public function test_comment_can_be_filled_with_data()
    {
        $comment = new Comment;
        $data = [
            'commentable_type' => 'App\\Models\\Post',
            'commentable_id' => 1,
            'user_id' => 1,
            'body' => 'This is a test comment',
            'status' => CommentStatusType::PENDING->value,
            'depth' => 0,
        ];

        $comment->fill($data);

        $this->assertEquals('App\\Models\\Post', $comment->commentable_type);
        $this->assertEquals(1, $comment->commentable_id);
        $this->assertEquals(1, $comment->user_id);
        $this->assertEquals('This is a test comment', $comment->body);
        $this->assertEquals(CommentStatusType::PENDING->value, $comment->status);
        $this->assertEquals(0, $comment->depth);
    }

    public function test_comment_can_be_filled_with_guest_data()
    {
        $comment = new Comment;
        $data = [
            'commentable_type' => 'App\\Models\\Post',
            'commentable_id' => 1,
            'guest_name' => 'Guest User',
            'guest_email' => 'guest@example.com',
            'guest_ip' => '127.0.0.1',
            'body' => 'This is a guest comment',
            'status' => CommentStatusType::PENDING->value,
            'depth' => 0,
        ];

        $comment->fill($data);

        $this->assertEquals('Guest User', $comment->guest_name);
        $this->assertEquals('guest@example.com', $comment->guest_email);
        $this->assertEquals('127.0.0.1', $comment->guest_ip);
        $this->assertEquals('This is a guest comment', $comment->body);
        $this->assertNull($comment->user_id);
    }

    public function test_comment_status_can_be_changed()
    {
        $comment = new Comment;
        $comment->status = CommentStatusType::PENDING->value;
        $this->assertEquals(CommentStatusType::PENDING->value, $comment->status);

        $comment->status = CommentStatusType::APPROVED->value;
        $this->assertEquals(CommentStatusType::APPROVED->value, $comment->status);

        $comment->status = CommentStatusType::SPAM->value;
        $this->assertEquals(CommentStatusType::SPAM->value, $comment->status);
    }

    public function test_nested_comment_can_be_created()
    {
        $parent = new Comment;
        $parent->fill([
            'commentable_type' => 'App\\Models\\Post',
            'commentable_id' => 1,
            'user_id' => 1,
            'body' => 'Parent comment',
            'status' => CommentStatusType::APPROVED->value,
            'depth' => 0,
        ]);

        $child = new Comment;
        $child->fill([
            'commentable_type' => 'App\\Models\\Post',
            'commentable_id' => 1,
            'user_id' => 1,
            'body' => 'Child comment',
            'parent_id' => $parent->id,
            'status' => CommentStatusType::APPROVED->value,
            'depth' => 1,
        ]);

        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertEquals(1, $child->depth);
    }

    public function test_reaction_can_be_created_with_user()
    {
        $reaction = new Reaction;
        $reaction->fill([
            'comment_id' => 1,
            'user_id' => 1,
            'type' => ReactionType::LIKE->value,
        ]);

        $this->assertEquals(1, $reaction->comment_id);
        $this->assertEquals(1, $reaction->user_id);
        $this->assertEquals(ReactionType::LIKE, $reaction->type);
        $this->assertNull($reaction->guest_fingerprint);
    }

    public function test_reaction_can_be_created_with_guest()
    {
        $reaction = new Reaction;
        $reaction->fill([
            'comment_id' => 1,
            'guest_fingerprint' => 'test_fingerprint_123',
            'type' => ReactionType::DISLIKE->value,
        ]);

        $this->assertEquals(1, $reaction->comment_id);
        $this->assertEquals('test_fingerprint_123', $reaction->guest_fingerprint);
        $this->assertEquals(ReactionType::DISLIKE, $reaction->type);
        $this->assertNull($reaction->user_id);
    }

    public function test_reaction_type_can_be_changed()
    {
        $reaction = new Reaction;
        $reaction->type = ReactionType::LIKE->value;
        $this->assertEquals(ReactionType::LIKE, $reaction->type);

        $reaction->type = ReactionType::DISLIKE->value;
        $this->assertEquals(ReactionType::DISLIKE, $reaction->type);
    }

    public function test_comment_status_enum_values()
    {
        $this->assertEquals('pending', CommentStatusType::PENDING->value);
        $this->assertEquals('approved', CommentStatusType::APPROVED->value);
        $this->assertEquals('spam', CommentStatusType::SPAM->value);
    }

    public function test_comment_status_enum_labels()
    {
        $this->assertEquals('Pending', CommentStatusType::PENDING->label());
        $this->assertEquals('Approved', CommentStatusType::APPROVED->label());
        $this->assertEquals('Spam', CommentStatusType::SPAM->label());
    }

    public function test_reaction_type_enum_values()
    {
        $this->assertEquals('like', ReactionType::LIKE->value);
        $this->assertEquals('dislike', ReactionType::DISLIKE->value);
    }

    public function test_guest_fingerprint_helper_exists()
    {
        $this->assertTrue(class_exists(GuestFingerprint::class));
        $this->assertTrue(method_exists(GuestFingerprint::class, 'generate'));
        $this->assertTrue(method_exists(GuestFingerprint::class, 'validate'));
    }

    public function test_comment_fillable_attributes()
    {
        $comment = new Comment;
        $fillable = $comment->getFillable();

        $expectedFillable = [
            'commentable_type',
            'commentable_id',
            'user_id',
            'guest_name',
            'guest_email',
            'guest_ip',
            'body',
            'parent_id',
            'status',
            'depth',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_reaction_fillable_attributes()
    {
        $reaction = new Reaction;
        $fillable = $reaction->getFillable();

        $expectedFillable = [
            'comment_id',
            'user_id',
            'guest_fingerprint',
            'type',
        ];

        $this->assertEquals($expectedFillable, $fillable);
    }

    public function test_comment_casts()
    {
        $comment = new Comment;
        $casts = $comment->getCasts();

        $this->assertArrayHasKey('depth', $casts);
        $this->assertEquals('integer', $casts['depth']);
    }

    public function test_reaction_casts()
    {
        $reaction = new Reaction;
        $casts = $reaction->getCasts();

        $this->assertArrayHasKey('type', $casts);
        $this->assertEquals(ReactionType::class, $casts['type']);
    }

    public function test_comment_status_enum_to_array()
    {
        $statusArray = CommentStatusType::toArray();

        $this->assertIsArray($statusArray);
        $this->assertArrayHasKey('pending', $statusArray);
        $this->assertArrayHasKey('approved', $statusArray);
        $this->assertArrayHasKey('spam', $statusArray);
        $this->assertEquals('Pending', $statusArray['pending']);
        $this->assertEquals('Approved', $statusArray['approved']);
        $this->assertEquals('Spam', $statusArray['spam']);
    }

    public function test_comment_status_enum_all_cases()
    {
        $allCases = CommentStatusType::all();

        $this->assertIsArray($allCases);
        $this->assertCount(3, $allCases);
        $this->assertContains(CommentStatusType::PENDING, $allCases);
        $this->assertContains(CommentStatusType::APPROVED, $allCases);
        $this->assertContains(CommentStatusType::SPAM, $allCases);
    }
}
