<?php

namespace Fiachehr\Comments\Tests;

use Fiachehr\Comments\Enums\CommentStatusType;
use Fiachehr\Comments\Enums\ReactionType;
use Fiachehr\Comments\Events\CommentCreated;
use Fiachehr\Comments\Events\ReactionToggled;
use Fiachehr\Comments\Helper\GuestFingerprint;
use Fiachehr\Comments\Models\Comment;
use Fiachehr\Comments\Models\Reaction;
use Fiachehr\Comments\Rules\GuestFingerPrintRule;
use Fiachehr\Comments\Services\CommentsService;
use Fiachehr\Comments\Services\ReactionService;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    public function test_comment_status_enum_values()
    {
        $this->assertEquals('pending', CommentStatusType::PENDING->value);
        $this->assertEquals('approved', CommentStatusType::APPROVED->value);
        $this->assertEquals('spam', CommentStatusType::SPAM->value);
    }

    public function test_reaction_type_enum_values()
    {
        $this->assertEquals('like', ReactionType::LIKE->value);
        $this->assertEquals('dislike', ReactionType::DISLIKE->value);
    }

    public function test_comment_model_fillable_attributes()
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

    public function test_reaction_model_fillable_attributes()
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

    public function test_comment_model_casts()
    {
        $comment = new Comment;
        $casts = $comment->getCasts();

        $this->assertArrayHasKey('depth', $casts);
        $this->assertEquals('integer', $casts['depth']);
    }

    public function test_reaction_model_casts()
    {
        $reaction = new Reaction;
        $casts = $reaction->getCasts();

        $this->assertArrayHasKey('type', $casts);
        $this->assertEquals(ReactionType::class, $casts['type']);
    }

    public function test_guest_fingerprint_helper_exists()
    {
        $this->assertTrue(class_exists(GuestFingerprint::class));
        $this->assertTrue(method_exists(GuestFingerprint::class, 'generate'));
        $this->assertTrue(method_exists(GuestFingerprint::class, 'validate'));
    }

    public function test_guest_fingerprint_rule_exists()
    {
        $this->assertTrue(class_exists(GuestFingerPrintRule::class));
        $this->assertTrue(method_exists(GuestFingerPrintRule::class, 'validate'));
    }

    public function test_comment_created_event_has_correct_properties()
    {
        $comment = new Comment;
        $event = new CommentCreated($comment);

        $this->assertInstanceOf(CommentCreated::class, $event);
        $this->assertEquals($comment, $event->comment);
    }

    public function test_reaction_toggled_event_has_correct_properties()
    {
        $reaction = new Reaction;
        $event = new ReactionToggled($reaction);

        $this->assertInstanceOf(ReactionToggled::class, $event);
        $this->assertEquals($reaction, $event->reaction);
    }

    public function test_comments_service_has_required_methods()
    {
        $service = new CommentsService;

        $this->assertTrue(method_exists($service, 'createComment'));
        $this->assertTrue(method_exists($service, 'approveComment'));
    }

    public function test_reaction_service_has_required_methods()
    {
        $service = new ReactionService;

        $this->assertTrue(method_exists($service, 'toggleReaction'));
    }

    public function test_has_comments_trait_has_required_methods()
    {
        $trait = new \ReflectionClass(\Fiachehr\Comments\Traits\HasComments::class);

        $this->assertTrue($trait->hasMethod('comments'));
        $this->assertTrue($trait->hasMethod('detachReaction'));
    }

    private function validateWithRule($rule, $value)
    {
        $errors = [];
        $rule->validate('test_attribute', $value, function ($message) use (&$errors) {
            $errors[] = $message;
        });

        return empty($errors);
    }
}
